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

// Corrected query to exclude teenant_id
$sql_rooms = "SELECT id, roomnumber, price, maxclients, status FROM rooms";
$result_rooms = $conn->query($sql_rooms);

$rooms = [];
if ($result_rooms) {
    if ($result_rooms->num_rows > 0) {
        while ($row = $result_rooms->fetch_assoc()) {
            $rooms[] = $row;
        }
    }
} else {
    echo "Error executing query: " . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_room'])) {
        $roomnumber = $_POST['roomnumber'];
        $price = $_POST['price'];
        $maxclients = $_POST['maxclients'];
        $status = "Unoccupied"; // Set default status

        if (empty($roomnumber) || empty($price)) {
            echo "Room Number and Price are required.";
            exit;
        }

        // Corrected query to exclude teenant_id
        $sql = "INSERT INTO rooms (roomnumber, price, maxclients, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdis", $roomnumber, $price, $maxclients, $status);

        if ($stmt->execute()) {
            header("Location: admin_rooms.php");
            exit();
        } else {
            echo "Error adding record: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['delete_room'])) {
        $room_id = $_POST['room_id'];
        $sql = "DELETE FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        if ($stmt->execute()) {
            header("Location: admin_rooms.php");
            exit();
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['update_room'])) {
        $room_id = $_POST['room_id'];
        $roomnumber = $_POST['roomnumber'];
        $price = $_POST['price'];
        $maxclients = $_POST['maxclients'];
        $status = $_POST['status'];

        $sql = "UPDATE rooms SET roomnumber = ?, price = ?, maxclients = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdisi", $roomnumber, $price, $maxclients, $status, $room_id);

        if ($stmt->execute()) {
            header("Location: admin_rooms.php");
            exit();
        } else {
            echo "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Rooms</title>
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
      background-color: #28a745; /* Bootstrap primary blue */
      color: white;
  }

  
  .delete-btn {
      background-color: #dc3545; /* Bootstrap danger red */
      color: white;
  }

  

  /* Modal Styles (Dark) */
  .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
  }
  .modal-content {
    background: #212529;
    margin: 10% auto;  
    padding: 20px;
    width: 400px;
    border-radius: 5px;
    color: #fff;
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
    <div class="admin-page">
        <div class="sidebar">
            <h2>HOSTEL</h2>
            
      <p>Welcome, Admin</p>
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
            <h1>Rooms</h1>
            <button class="add-room-button" onclick="openAddModal()">Add Room</button>
            <div class="search-bar">
                Search: <input type="text" id="search-input" onkeyup="searchTable()">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Room Number</th>
                        <th>Price</th>
                        <th>Max Clients</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="rooms-table-body">
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['id']); ?></td>
                            <td><?php echo htmlspecialchars($room['roomnumber']); ?></td>
                            <td><?php echo htmlspecialchars($room['price']); ?></td>
                            <td><?php echo htmlspecialchars($room['maxclients']); ?></td>
                            <td><?php echo htmlspecialchars($room['status']); ?></td>
                            <td class="action-buttons">
                                <button  class="edit-btn"  onclick="openEditModal(<?php echo $room['id']; ?>, '<?php echo $room['roomnumber']; ?>', <?php echo $room['price']; ?>, <?php echo $room['maxclients']; ?>, '<?php echo $room['status']; ?>')">Edit</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                    <button class="delete-btn" type="submit" name="delete_room">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none';">&times;</span>
            <h2>Add Room</h2>
            <form method="POST" action="admin_rooms.php">
                <div class="form-group">
                    <label for="roomnumber">Room Number:</label>
                    <input type="text" id="addroomnumber" name="roomnumber" required>
                </div>
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="addPrice" name="price" required>
                </div>
                <div class="form-group">
                    <label for="maxclients">Max Clients:</label>
                    <input type="number" id="addmaxclients" name="maxclients">
                </div>
                <button type="submit" name="add_room" class="save-btn">Add</button>
            </form>
        </div>
    </div>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none';">&times;</span>
            <h2>Edit Room</h2>
            <form method="POST" action="admin_rooms.php">
                <input type="hidden" id="edit_room_id" name="room_id">
                <div class="form-group">
                    <label for="edit_roomnumber">Room Number:</label>
                    <input type="text" id="edit_roomnumber" name="roomnumber" required>
                </div>
                <div class="form-group">
                    <label for="edit_price">Price:</label>
                    <input type="number" id="edit_price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="edit_maxclients">Max Clients:</label>
                    <input type="number" id="edit_maxclients" name="maxclients">
                </div>
                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status">
                        <option value="Unoccupied">Unoccupied</option>
                        <option value="Occupied">Occupied</option>
                    </select>
                </div>
                <button type="submit" name="update_room" class="save-btn">Update</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(id, roomnumber, price, maxclients, status) {
            document.getElementById('edit_room_id').value = id;
            document.getElementById('edit_roomnumber').value = roomnumber;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_maxclients').value = maxclients;
            document.getElementById('edit_status').value = status;
            document.getElementById('editModal').style.display = 'block';
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.close').forEach(el => {
                el.onclick = function () {
                    el.closest('.modal').style.display = 'none';
                };
            });
        });

        function searchTable() {
            let input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search-input");
            filter = input.value.toUpperCase();
            table = document.getElementById("rooms-table-body");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                let found = false;
                for (let j = 0; j < tr[i].cells.length - 1; j++) {
                    td = tr[i].cells[j];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                if (found) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>