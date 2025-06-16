<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "umojahostel_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch checkins
$sql_checkins = "SELECT * FROM checkins";
$result_checkins = $conn->query($sql_checkins);

$checkins = [];
if ($result_checkins->num_rows > 0) {
    while ($row = $result_checkins->fetch_assoc()) {
        $checkins[] = $row;
    }
}

// Delete checkin
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM checkins WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: admin_checkin.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
    $stmt->close();
}

// Edit checkin (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_checkins'])) {
    $checkinId = $_POST['edit_checkin_id'];
    $firstName = $_POST['firstName'];
    $secondName = $_POST['secondName'];
    $email = $_POST['email'];
    $room_number = $_POST['room_number'];
    $checkin_datetime = $_POST['checkin_datetime'];
    $checkout_datetime = $_POST['checkout_datetime'];
    $status = $_POST['status'];

    if (empty($firstName) || empty($email)) {
        echo "First name and email are required.";
        exit;
    }

    $sql = "UPDATE checkins SET first_name = ?, second_name = ?, email = ?, room_number = ?, checkin_datetime = ?, checkout_datetime = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $firstName, $secondName, $email, $room_number, $checkin_datetime, $checkout_datetime, $status, $checkinId);

    if ($stmt->execute()) {
        header("Location: admin_checkin.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch checkin for editing
$checkin_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql_edit = "SELECT * FROM checkins WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows == 1) {
        $checkin_to_edit = $result_edit->fetch_assoc();
    }
    $stmt_edit->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Checkins</title>
    <style>
       /* Overall body styles */
  body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f8f9fa; /* Light overall background */
      color: #212529;
  }

  .admin-page {
      display: flex;
      min-height: 100vh;
  }

  /* Sidebar & Profile Header (Dark) */
  .sidebar {
      width: 200px;
      background-color: #212529; /* Dark sidebar background */
      padding: 20px;
      color: #fff;
  }
  
  .profile-picture {
    width: 70px;    /* Adjust the size as needed */
    height: 70px;   /* Ensure height is equal to width */
    border-radius: 50%; /* Makes the image perfectly round */
    border: 3px solid #fff; /* Optional: a white border for contrast */
    margin-bottom: 10px; 
    margin-left: 40px;
}

  
  .sidebar h2 {
      color: #fff;
  }
  .sidebar ul {
      list-style: none;
      padding: 0;
      margin-top: 20px;
  }
  .sidebar li {
      margin-bottom: 10px;
  }
  .sidebar a {
      text-decoration: none;
      display: block;
      background: #343a40;
      padding: 10px;
      border-radius: 5px;
      color: #fff;
      transition: background 0.3s ease;
  }
  .sidebar a:hover {
      background: #495057;
  }

  /* Main Content (Light) */
  .main-content {
      flex: 1;
      padding: 20px;
      background-color: #ffffff; /* White background */
      color: #212529;
      box-shadow: -3px 0 10px rgba(0, 0, 0, 0.1); /* Subtle left shadow */
  }

  /* Table Styles */
  table {
      width: 100%;
      border-collapse: collapse;
  }
  th, td {
      padding: 8px;
      border: 1px solid #dee2e6;
  }
  th {
      background-color: #e9ecef;
  }
  .action-buttons button {
      margin: 0 5px;
      padding: 6px 12px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
  }
  .edit-btn {
      background-color: #4CAF50;
      color: white;
  }
  .delete-btn {
      background-color: #f44336;
      color: white;
  }

  /* Modal Styles (Dark) */
  .modal {
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 10;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh; /* Ensure it's exactly viewport height */
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    margin-left: 390px;
}


.modal-content {
    background: #212529;
    padding: 20px;
    width: 400px;
    border-radius: 5px;
    color: #fff;
    max-height: 90vh;
    overflow-y: auto;
    margin: 0; /* Ensure there are no unintended margins */
}
  .close {
      float: right;
      font-size: 24px;
      cursor: pointer;
  }
  .form-group {
      margin-bottom: 20px;
  }
  .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #fff;
  }
  .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      box-sizing: border-box;
      background-color: #f8f9fa;
      color: #212529;
  }
  .save-btn {
      background-color: #4CAF50;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
  }
  .save-btn:hover {
      background-color: #45a049;
  }
  .search-bar {
      margin-bottom: 20px;
  }
  .search-bar input {
      padding: 8px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      background-color: #ffffff;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
      color: #212529;
  }
    </style>
</head>
<body>

<div id="editModal" class="modal" style="<?= $checkin_to_edit ? 'display:block;' : 'display:none;' ?>">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none';">&times;</span>
        <h2>Edit Checkin</h2>
        <form method="POST" action="admin_checkin.php">
            <input type="hidden" id="edit_checkin_id" name="edit_checkin_id" value="<?= $checkin_to_edit ? $checkin_to_edit['id'] : '' ?>">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" value="<?= $checkin_to_edit['first_name'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="secondName">Second Name:</label>
                <input type="text" id="secondName" name="secondName" value="<?= $checkin_to_edit['second_name'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= $checkin_to_edit['email'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="room_number">Room Number:</label>
                <input type="text" id="room_number" name="room_number" value="<?= $checkin_to_edit['room_number'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="checkin_datetime">Checkin Datetime:</label>
                <input type="datetime-local" id="checkin_datetime" name="checkin_datetime" value="<?= $checkin_to_edit['checkin_datetime'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="checkout_datetime">Checkout Datetime:</label>
                <input type="datetime-local" id="checkout_datetime" name="checkout_datetime" value="<?= $checkin_to_edit['checkout_datetime'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <input type="text" id="status" name="status" value="<?= $checkin_to_edit['status'] ?? '' ?>">
            </div>
            <button type="submit" name="edit_checkins" class="save-btn">Save</button>
        </form>
    </div>
</div>

<div class="admin-page">
    <div class="sidebar">
        <h2>Umoja Hostel</h2>
        <p>Admin</p>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_rooms.php">Rooms</a></li>
            <li><a href="admin_requests.php">Requests</a></li>
            <li><a href="admin_checkin.php">Checkin</a></li>
            <li><a href="admin_checkout.php">Checkout</a></li>
            <br>
            <li class="logout-button"><a href="../php/logout.php">Logout</a></li>
        </ul>
        
    </div>
    <div class="main-content">
        <h1>Checkins</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Second Name</th>
                    <th>Email</th>
                    <th>Room Number</th>
                    <th>Checkin Datetime</th>
                    <th>Checkout Datetime</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checkins as $checkin): ?>
                    <tr>
                        <td><?= $checkin['id']; ?></td>
                        <td><?= $checkin['first_name']; ?></td>
                        <td><?= $checkin['second_name']; ?></td>
                        <td><?= $checkin['email']; ?></td>
                        <td><?= $checkin['room_number']; ?></td>
                        <td><?= $checkin['checkin_datetime']; ?></td>
                        <td><?= $checkin['checkout_datetime']; ?></td>
                        <td><?= $checkin['status']; ?></td>
                        <td class="action-buttons">
                            <button onclick="openEditModal(<?= $checkin['id']; ?>)" class="edit-btn">Edit</button>
                            <button onclick="confirmDelete(<?= $checkin['id']; ?>)" class="delete-btn">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function openEditModal(checkinId) {
        // Fetch checkin data and populate the modal
        fetch('admin_checkin.php?edit_id=' + checkinId)
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const htmlDoc = parser.parseFromString(data, 'text/html');
                const checkin = JSON.parse(htmlDoc.getElementById('checkin_data').textContent);

                document.getElementById('edit_checkin_id').value = checkin.id;
                document.getElementById('firstName').value = checkin.first_name;
                document.getElementById('secondName').value = checkin.second_name;
                document.getElementById('email').value = checkin.email;
                document.getElementById('room_number').value = checkin.room_number;
                document.getElementById('checkin_datetime').value = checkin.checkin_datetime;
                document.getElementById('checkout_datetime').value = checkin.checkout_datetime;
                document.getElementById('status').value = checkin.status;
                document.getElementById('editModal').style.display = 'block';
            });
    }

    function confirmDelete(checkinId) {
        if (confirm("Are you sure you want to delete this checkin record?")) {
            window.location.href = "admin_checkin.php?delete_id=" + checkinId;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Close modal
        document.querySelectorAll('.close').forEach(el => {
            el.onclick = function () {
                el.closest('.modal').style.display = 'none';
            };
        });
    });
</script>

<?php if ($checkin_to_edit): ?>
    <div id="checkin_data" style="display: none;">
        <?= json_encode($checkin_to_edit); ?>
    </div>
<?php endif; ?>
</body>
</html>