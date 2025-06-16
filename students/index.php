  <?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include __DIR__ . '/../config.php';

// Display success message
if (isset($_SESSION['logout_success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['logout_success'] . '</div>';
    unset($_SESSION['logout_success']);
}

// Display error message
if (isset($_SESSION['logout_error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['logout_error'] . '</div>';
    unset($_SESSION['logout_error']);
}

// Check if the student has already booked a room (using session)
$tenant_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
$has_booked_room = false;
$booked_room = null;

if ($tenant_id) {
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
    if ($stmt_booked_room->num_rows > 0) {
        $stmt_booked_room->bind_result($roomnumber);
        $stmt_booked_room->fetch();
        $has_booked_room = true;
        $booked_room = ['roomnumber' => $roomnumber];
    }
    $stmt_booked_room->close();
}

// Fetch available rooms for the "Book Room" form (only if the student hasn't booked a room)
$rooms = [];
if (!$has_booked_room) {
    $sql_rooms = "SELECT id, roomnumber, price, maxclients FROM rooms WHERE status = 'unoccupied'";
    $result_rooms = $conn->query($sql_rooms);
    if ($result_rooms === false) {
        die("Query failed: " . $conn->error);
    }
    if ($result_rooms->num_rows > 0) {
        while ($row = $result_rooms->fetch_assoc()) {
            $rooms[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>
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
            margin-bottom: 20px;
        }
        .form-card h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-card input, .form-card select {
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
        .book-room-form {
            display: <?php echo $has_booked_room ? 'none' : 'block'; ?>;
        }
        .post-booking-options {
            display: <?php echo $has_booked_room ? 'block' : 'none'; ?>;
        }
        .form-card input[readonly] {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">🏡Welcome Aboard!</h1>

        <?php
        if (isset($_SESSION['book_success'])) {
            echo "<p class='text-success text-center'>" . $_SESSION['book_success'] . "</p>";
            unset($_SESSION['book_success']);
        }
        if (isset($_SESSION['book_error'])) {
            echo "<p class='text-danger text-center'>" . $_SESSION['book_error'] . "</p>";
            unset($_SESSION['book_error']);
        }
        if (isset($_SESSION['logout_success'])) {
            echo "<p class='text-success text-center'>" . $_SESSION['logout_success'] . "</p>";
            unset($_SESSION['logout_success']);
        }
        ?>

        <!-- Book Room Form (shown only if no room is booked) -->
        <div class="form-card book-room-form">
            <h2>Book Room</h2>
            <form action="book_room.php" method="POST" onsubmit="return validateBookRoomForm()">
                <input type="text" name="firstname" id="firstname" placeholder="First name" required>
                <input type="text" name="lastname" id="lastname" placeholder="Last name">
                <input type="email" name="email" id="email" placeholder="Email" required>
                <select name="room_id" id="room_id" required>
                    <option value="">Select Room Number</option>
                    <?php if (empty($rooms)): ?>
                        <option value="" disabled>No rooms available</option>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>" 
                                    data-price="<?php echo $room['price']; ?>"
                                    data-maxclients="<?php echo $room['maxclients']; ?>">
                                <?php echo htmlspecialchars($room['roomnumber']) . ' (Price: ' . $room['price'] . ', Max: ' . $room['maxclients'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <input type="text" id="price" value="" readonly placeholder="Price">
                <input type="text" id="maxclients" value="" readonly placeholder="Max Clients">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <button type="submit">Book now</button>
                <br>
                <div class="w-full text-center">
                <a class="inline-block link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" href="../index.html">
                    Back to home
                </a>
                </div>

            </form>
        </div>

        <!-- Post-Booking Options (shown only if a room is booked) -->
        <div class="post-booking-options">
            <div class="form-card">
                <h2>Your Room</h2>
                <p>You have booked Room: <strong><?php echo htmlspecialchars($booked_room['roomnumber'] ?? ''); ?></strong></p>
                <a href="maintenance_request.php" class="btn btn-success w-100 mb-2">Submit Maintenance Request</a>
                <a href="view_room_status.php" class="btn btn-primary w-100 mb-2">View Room Status</a>
                <a href="view_maintenance_requests.php" class="btn btn-primary w-100 mb-2">View Maintenance Requests</a>
                <a href="logout.php" class="btn btn-danger w-100 mb-2">Check Out</a>
                <br>
                <a class="link-offset-2 link-offset-3-hover  link-underline link-underline-opacity-0 link-underline-opacity-75-hover " href="../index.html">
                        Back to Home
                        </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function validateBookRoomForm() {
            const firstname = document.getElementById('firstname').value;
            const email = document.getElementById('email').value;
            const roomId = document.getElementById('room_id').value;
            const password = document.getElementById('password').value;

            if (!firstname) {
                alert('Please enter your first name.');
                return false;
            }
            if (!email) {
                alert('Please enter your email.');
                return false;
            }
            if (!roomId) {
                alert('Please select a room to book.');
                return false;
            }
            if (!password) {
                alert('Please enter a password.');
                return false;
            }
            return true;
        }

        // Update the price and maxclients fields when a room is selected
        const roomSelect = document.getElementById('room_id');
        const priceInput = document.getElementById('price');
        const maxclientsInput = document.getElementById('maxclients');

        roomSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            priceInput.value = selectedOption.getAttribute('data-price') || '';
            maxclientsInput.value = selectedOption.getAttribute('data-maxclients') || '';
        });

        // Initialize fields if there's a selected option
        if (roomSelect.selectedIndex > 0) {
            const initialOption = roomSelect.options[roomSelect.selectedIndex];
            priceInput.value = initialOption.getAttribute('data-price') || '';
            maxclientsInput.value = initialOption.getAttribute('data-maxclients') || '';
        }
    </script>
</body>
</html>