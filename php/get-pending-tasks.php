<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'maintenancestaff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Fetch pending and in-progress tasks
$query = "SELECT id, request_detail FROM maintenance_requests WHERE status IN ('pending', 'in progress')";
$result = $conn->query($query);

$tasks = []; //stores the tasks
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) { //retrieves the next row from the result as an associative array
        $tasks[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($tasks);
?>
