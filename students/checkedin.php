<?php
session_start();
include __DIR__ . '/../config.php';

// Simulated login (for testing)
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1; // Simulate a student logged in
}

$has_booked_room = false;
$booked_room = [];

// Check if student has booked a room
if (isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT room_number FROM checkins WHERE id = ? AND status = 'checked in'");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $has_booked_room = true;
        $booked_room = $result->fetch_assoc();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .form-card {
            padding: 20px;
            margin: 20px auto;
            width: 80%;
            max-width: 600px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        .btn {
            display: block;
            text-align: center;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }

        .btn-success { background-color: green; }
        .btn-primary { background-color: blue; }
        .btn-danger  { background-color: red; }
    </style>
</head>
<body>

<!-- Welcome and Booking Info -->
<div class="form-card">
    <h2>Welcome!</h2>
    <?php if ($has_booked_room): ?>
        <p>You’ve already booked a room.</p>
        <p><strong>Room Number:</strong> <?php echo htmlspecialchars($booked_room['room_number']); ?></p>
    <?php else: ?>
        <p>You have not booked a room yet.</p>
    <?php endif; ?>
</div>

<!-- Book a Room Form (only if not booked) -->
<?php if (!$has_booked_room): ?>
    <div class="form-card">
        <h2>Book a Room</h2>
        <form method="post" action="book-room.php">
            <label>Room Number:</label><br>
            <input type="text" name="room_number" required><br><br>
            <input type="submit" value="Book Room">
        </form>
    </div>
<?php endif; ?>

<!-- Post-Booking Options (only if booked) -->
<?php if ($has_booked_room): ?>
    <div class="post-booking-options">
        <div class="form-card">
            <h2>Your Room</h2>
            <p>You have booked Room: <strong><?php echo htmlspecialchars($booked_room['room_number']); ?></strong></p>

            <a href="maintenance_request.php" class="btn btn-success w-100 mb-2 fw-normal">Submit Maintenance Request</a>
            <a href="view_room_status.php" class="btn btn-primary w-100 mb-2">View Room Status</a>
            <a href="view_maintenance_requests.php" class="btn btn-primary w-100 mb-2">View Maintenance Requests</a>
            <a href="logout.php" class="btn btn-danger w-100 mb-2">Check Out</a>

                <br>
                <a class="link-offset-2 link-offset-3-hover  link-underline link-underline-opacity-0 link-underline-opacity-75-hover " href="../index.html">
                        Back to Home
                        </a>
            </div>
    </div>
<?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
 integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
