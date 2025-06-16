<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include __DIR__ . '/../config.php';


if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Get tenant's room number from checkins table
$stmt = $conn->prepare("SELECT room_number FROM checkins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$room_result = $stmt->get_result();

if ($room_result->num_rows === 0) {
    die("No room booking found");
}

$room_data = $room_result->fetch_assoc();
$tenant_room = $room_data['room_number'];

// Get maintenance requests for this room
$stmt_requests = $conn->prepare("SELECT 
    id AS request_id,
    request_detail,
    room_location,
    status,
    submitted_at
FROM maintenance_requests
WHERE room_location = ?");

$stmt_requests->bind_param("s", $tenant_room);
$stmt_requests->execute();
$result = $stmt_requests->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Maintenance Requests for Room <?= htmlspecialchars($tenant_room) ?></h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Request Details</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['request_id']) ?></td>
                                <td><?= htmlspecialchars($row['request_detail']) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $row['status'] === 'pending' ? 'bg-warning' : 
                                           ($row['status'] === 'completed' ? 'bg-success' : 'bg-primary') ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y g:i a', strtotime($row['submitted_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No maintenance requests found for your room.</div>
        <?php endif; ?>
        
        <a href="checkedin.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>