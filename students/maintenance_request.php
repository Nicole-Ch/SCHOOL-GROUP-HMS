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


// Check if the student has booked a room
$sql_booked_room = "SELECT room_number 
                    FROM checkins 
                    WHERE id = ? AND status = 'checked in'";
$stmt_booked_room = $conn->prepare($sql_booked_room);
if ($stmt_booked_room === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_booked_room->bind_param("i", $tenant_id);
$stmt_booked_room->execute();
$stmt_booked_room->store_result();
if ($stmt_booked_room->num_rows == 0) {
    $_SESSION['maintenance_error'] = "You need to book a room before submitting a maintenance request.";
    header("Location: index.php");
    exit();
}
$stmt_booked_room->bind_result($roomnumber);
$stmt_booked_room->fetch();
$booked_room = $roomnumber;
$stmt_booked_room->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request - Hostel Management System</title>
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
        .form-card {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-card h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-card textarea, .form-card input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            background-color: #f0f0f0;
        }
        .form-card button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .form-card button:hover {
            background-color: #218838;
        }
        .form-card input[readonly] {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Maintenance Request</h1>

        <?php
        if (isset($_SESSION['maintenance_success'])) {
            echo "<p class='text-success text-center'>" . $_SESSION['maintenance_success'] . "</p>";
            unset($_SESSION['maintenance_success']);
        }
        if (isset($_SESSION['maintenance_error'])) {
            echo "<p class='text-danger text-center'>" . $_SESSION['maintenance_error'] . "</p>";
            unset($_SESSION['maintenance_error']);
        }
        ?>

        <div class="form-card">
            <h2>Submit Maintenance Request</h2>
            <form action="submit_maintenance_request.php" method="POST" onsubmit="return validateMaintenanceForm()">
                <textarea name="request_detail" id="request_detail" placeholder="maintenance request" required>oh my tap is broken</textarea>
                <input type="text" name="room_location" value="<?php echo htmlspecialchars($booked_room); ?>" readonly>
                <button type="submit">submit now</button>
            </form>
            <div class="mt-3 text-center">
                <a href="checkedin.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function validateMaintenanceForm() {
            const requestDetail = document.getElementById('request_detail').value;
            if (!requestDetail.trim()) {
                alert('Please enter a maintenance request.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>