<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include __DIR__ . '/../config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $room_id = $_POST['room_id'];

    // Validate room ID
    if (!is_numeric($room_id)) {
        $_SESSION['book_error'] = "Invalid room selection";
        header("Location: index.php");
        exit();
    }
    $room_id = (int)$room_id;

    // Basic input validation
    $required = ['firstname', 'email', 'password', 'room_id'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['book_error'] = "All required fields must be filled.";
            header("Location: index.php");
            exit();
        }
    }

    // Fetch room details
    $stmt_room = $conn->prepare("SELECT roomnumber, status FROM rooms WHERE id = ?");
    if (!$stmt_room) {
        $_SESSION['book_error'] = "Database error: " . $conn->error;
        header("Location: index.php");
        exit();
    }

    $stmt_room->bind_param("i", $room_id);
    $stmt_room->execute();
    $stmt_room->store_result();

    if ($stmt_room->num_rows === 0) {
        $_SESSION['book_error'] = "Invalid room selected.";
        $stmt_room->close();
        header("Location: index.php");
        exit();
    }

    $stmt_room->bind_result($roomnumber, $current_status);
    $stmt_room->fetch();
    $stmt_room->close();

    // Check current status
    if ($current_status !== 'unoccupied') {
        $_SESSION['book_error'] = "Room is no longer available.";
        header("Location: index.php");
        exit();
    }

    // Check email uniqueness
    $stmt_email = $conn->prepare("SELECT id FROM checkins WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();

    if ($stmt_email->num_rows > 0) {
        $_SESSION['book_error'] = "Email already in use.";
        $stmt_email->close();
        header("Location: index.php");
        exit();
    }
    $stmt_email->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Transaction setup
    $conn->begin_transaction();

    try {
        // Lock row for update
        $conn->query("SELECT id FROM rooms WHERE id = $room_id FOR UPDATE");

        // Update room status
        $stmt_update = $conn->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ? AND status = 'unoccupied'");
        $stmt_update->bind_param("i", $room_id);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Update failed: " . $stmt_update->error);
        }

        if ($stmt_update->affected_rows === 0) {
            throw new Exception("Room booking conflict - already occupied");
        }

        // Insert checkin record
        $stmt_checkin = $conn->prepare("INSERT INTO checkins 
    (first_name, second_name, email, password, room_number, checkin_datetime, status) 
    VALUES (?, ?, ?, ?, ?, NOW(), 'checked in')");
        
        $stmt_checkin->bind_param("sssss", $firstname, $lastname, $email, $hashed_password, $roomnumber);
        
        if (!$stmt_checkin->execute()) {
            throw new Exception("Checkin failed: " . $stmt_checkin->error);
        }

        $tenant_id = $stmt_checkin->insert_id;
        
        // Set session variables
        $_SESSION['tenant_id'] = $tenant_id;
        $_SESSION['tenant_name'] = $firstname;
        $_SESSION['email'] = $email;
        $_SESSION['roomnumber'] = $roomnumber;

        $conn->commit();
        $_SESSION['book_success'] = "Room $roomnumber booked successfully!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['book_error'] = $e->getMessage();
        error_log("Booking Error: " . $e->getMessage() . " [Room ID: $room_id]");
    }

    $conn->close();
    header("Location: index.php");
    exit();
}

ob_end_flush();