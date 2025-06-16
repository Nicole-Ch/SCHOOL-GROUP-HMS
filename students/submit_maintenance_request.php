<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Ensure that tenant is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$tenant_id = $_SESSION['id'];
include __DIR__ . '/../config.php';

// Debugging: Display tenant ID to verify session
echo "Tenant ID: " . $tenant_id . "<br>"; // Debugging

// Check for the tenant's booked room
$sql_room = "SELECT room_number, status 
             FROM checkins 
             WHERE id = ? AND status = 'checked in'";
$stmt_room = $conn->prepare($sql_room);
if ($stmt_room === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt_room->bind_param("i", $tenant_id);
$stmt_room->execute();
$stmt_room->store_result();

if ($stmt_room->num_rows == 0) {
    $_SESSION['maintenance_error'] = "You need to book a room before submitting a maintenance request.";
    header("Location: index.php");
    exit();
}

$stmt_room->bind_result($roomnumber, $status);
$stmt_room->fetch();

// Debugging: Display the room number and status
echo "Room Number: " . $roomnumber . " | Status: " . $status . "<br>"; // Debugging

$room_location = $roomnumber;
$stmt_room->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_detail = $_POST['request_detail'];

    // Ensure the request detail is not empty
    if (empty($request_detail)) {
        $_SESSION['maintenance_error'] = "Please provide details for the maintenance request.";
        header("Location: maintenance_request.php");
        exit();
    }

    // Insert maintenance request into the database
    $sql_insert = "INSERT INTO maintenance_requests (request_detail, room_location, status, submitted_at) 
                   VALUES (?, ?, 'pending', NOW())";
    $stmt_insert = $conn->prepare($sql_insert);

    if ($stmt_insert === false) {
        $_SESSION['maintenance_error'] = "Database error: " . $conn->error;
        header("Location: maintenance_request.php");
        exit();
    }

    $stmt_insert->bind_param("ss", $request_detail, $room_location);
    
    // Debugging: Check if the insert query was executed successfully
    if ($stmt_insert->execute()) {
        $_SESSION['maintenance_success'] = "Maintenance request submitted successfully!";
    } else {
        $_SESSION['maintenance_error'] = "Failed to submit the maintenance request: " . $stmt_insert->error;
    }

    $stmt_insert->close();
}

$conn->close();

// Redirect back to maintenance request page
header("Location: maintenance_request.php");
exit();
?>
