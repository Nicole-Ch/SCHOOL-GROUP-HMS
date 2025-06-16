<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$tenant_id = $_SESSION['id'];
include __DIR__ . '/../config.php'; 

$conn->begin_transaction();

try {
    // 1. Get room number from checkins
    $stmt_room = $conn->prepare("SELECT room_number FROM checkins WHERE id=? AND status='checked in'");
    if (!$stmt_room) throw new Exception("Prepare failed: " . $conn->error);
    
    $stmt_room->bind_param("i", $tenant_id);
    $stmt_room->execute();
    $stmt_room->store_result();
    
    if ($stmt_room->num_rows === 0) throw new Exception("No active booking found");
    
    $stmt_room->bind_result($roomnumber);
    $stmt_room->fetch();
    $stmt_room->close();

    // 2. Update checkins record
    $stmt_checkout = $conn->prepare("UPDATE checkins 
                                   SET status='Checked Out', checkout_datetime=NOW() 
                                   WHERE id=?");
    if (!$stmt_checkout) throw new Exception("Checkout prepare failed: " . $conn->error);
    
    $stmt_checkout->bind_param("i", $tenant_id);
    if (!$stmt_checkout->execute()) throw new Exception("Checkout failed: " . $stmt_checkout->error);
    $stmt_checkout->close();

    // 3. Update room status
    $stmt_room = $conn->prepare("UPDATE rooms SET status='unoccupied' WHERE roomnumber=?");
    if (!$stmt_room) throw new Exception("Room update prepare failed: " . $conn->error);
    
    $stmt_room->bind_param("s", $roomnumber);
    if (!$stmt_room->execute()) throw new Exception("Room update failed: " . $stmt_room->error);
    $stmt_room->close();

    $conn->commit();

    // Keep session alive but remove tenant_id
    unset($_SESSION['tenant_id']); // Remove sensitive data
    $_SESSION['logout_success'] = "You have successfully checked out!"; // Set message

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['logout_error'] = $e->getMessage(); // Store error in session
}

$conn->close();
header("Location: index.php"); // Redirect AFTER preserving session
exit();
?>