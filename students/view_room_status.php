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


//  Use a single prepared statement and proper variable handling
$stmt = $conn->prepare("SELECT 
    c.room_number AS tenant_room,
    r.roomnumber AS official_room,
    r.price,
    r.maxclients,
    c.checkin_datetime,
    c.status
FROM checkins c
INNER JOIN rooms r ON c.room_number = r.roomnumber
WHERE c.id = ?");

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

//  Bind parameters to the actual statement
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

//  Proper result handling
if ($result->num_rows == 0) {
    header("Location: index.php"); //  Removed unnecessary ../
    exit();
}

//  Fetch all needed data
$room_data = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Room Status - Hostel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Room Status</h1>
        <div class="card">
            <h2>Your Room Details</h2>
            <!--  Show all available data -->
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room_data['official_room']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($room_data['status']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($room_data['price']); ?></p>
            <p><strong>Max Clients:</strong> <?php echo htmlspecialchars($room_data['maxclients']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($room_data['checkin_datetime']); ?></p>
            <div class="mt-3 text-center">
                <a href="checkedin.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>