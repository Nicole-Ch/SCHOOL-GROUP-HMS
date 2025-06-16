<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

   
    $sql = "UPDATE maintenance_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql); //prepare sends the query to the database for validation
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        // Optionally, set a session message for feedback
        $_SESSION['message'] = "Task updated successfully!";
        header("Location: maintenancestaff.php"); // Redirect back to the dashboard
        exit();
    } else {
        echo "Error updating request: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>
