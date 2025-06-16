<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "umojahostel_db";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Fetch Requests
$conn = getDbConnection();
$sql_requests = "SELECT * FROM maintenance_requests";

// search functionality
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql_requests = "SELECT * FROM maintenance_requests 
                     WHERE room_location LIKE '%$search%' 
                     OR request_detail LIKE '%$search%' 
                     OR status LIKE '%$search%'";
}

$result_requests = $conn->query($sql_requests);

$requests = [];
if ($result_requests) {
    if ($result_requests->num_rows > 0) {
        while ($row = $result_requests->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    $result_requests->free_result();
} else {
    echo "Error executing query: " . $conn->error;
}

$conn->close();


// Handle status update for requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['status'])) {
    $conn = getDbConnection();
    
    $requestId = $_POST['request_id'];
    $status = $_POST['status']; // Either "In Progress", "Completed", or "Pending"

    $sql = "UPDATE maintenance_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $requestId);

    if ($stmt->execute()) {
        echo "<script>window.location.href='admin_requests.php';</script>";
        exit();
    } else {
        echo "Error updating status: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}


// Handle request editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_request_id']) && isset($_POST['room_location']) && isset($_POST['request_detail'])) {
    $conn = getDbConnection();

    $requestId = $_POST['edit_request_id'];
    $room_location = $_POST['room_location'];
    $request_detail = $_POST['request_detail'];

    $sql = "UPDATE maintenance_requests SET room_location = ?, request_detail = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $room_location, $request_detail, $requestId);

    if ($stmt->execute()) {
        echo "<script>alert('Request updated successfully.'); window.location.href='admin_requests.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating request: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    $conn->close();
}

// Handle request deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_request_id'])) {
    $conn = getDbConnection();

    $requestId = $_POST['delete_request_id'];

    $sql = "DELETE FROM maintenance_requests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $requestId);

    if ($stmt->execute()) {
        echo "<script>alert('Request deleted successfully.'); window.location.href='admin_requests.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting request: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Requests</title>
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
      background-color: #007bff; /* Bootstrap primary blue */
      color: white;
  }

 
  .delete-btn {
      background-color: #dc3545; /* Bootstrap danger red */
      color: white;
  }

  
  .approve-btn {
      background-color: #28a745; /* Bootstrap success green */
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
      margin: 15% auto;
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
                <li><a href="admin_requests.php" class="active">Request</a></li>
                <li><a href="admin_checkin.php">Checkin</a></li>
                <li><a href="admin_checkout.php">Checkout</a></li>
                <br>
            <li class="logout-button"><a href="../php/logout.php">Logout</a></li>
            </ul>
            
        </div>
        <div class="main-content">
            <h1>Requests</h1>
            <div class="search-bar">
                <form method="GET" action="">
                    Search: <input type="text" name="search" id="search-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Room Location</th>
                        <th>Request Detail</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['room_location']); ?></td>
                                <td><?php echo htmlspecialchars($request['request_detail']); ?></td>
                                <td><?php echo htmlspecialchars($request['status']); ?></td>
                                <td class="action-buttons">
                                    <button  class="approve-btn" onclick="openStatusModal(<?php echo $request['id']; ?>)">Approve</button>
                                    <button class="edit-btn"  onclick="openEditModal(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['room_location']); ?>', '<?php echo htmlspecialchars($request['request_detail']); ?>')">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this request?');">
                                        <input type="hidden" name="delete_request_id" value="<?php echo $request['id']; ?>">
                                        <button class="delete-btn" type="submit" style="background-color: #f44336;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">&times;</span>
            <h2>Handle Request</h2>
            <form method="POST" action="">
                <input type="hidden" id="requestId" name="request_id">
                <select name="status" id="status">
                    <option value="Pending">in progress</option>
                    <option value="Approved">pending</option>
                    <option value="Fixed">completed</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content edit-modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Request</h2>
            <form method="POST" action="">
                <input type="hidden" id="editRequestId" name="edit_request_id">
                <input type="text" id="editRoomLocation" name="room_location" placeholder="Room Location">
                <textarea id="editRequestDetail" name="request_detail" placeholder="Request Detail"></textarea>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openStatusModal(requestId) {
    document.getElementById('requestId').value = requestId;
    // Preselect "Pending" in the dropdown.
    document.getElementById('status').value = 'Pending';
    document.getElementById('statusModal').style.display = 'block';
}

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        function openEditModal(requestId, room_location, request_detail) {
            document.getElementById('editRequestId').value = requestId;
            document.getElementById('editRoomLocation').value = room_location;
            document.getElementById('editRequestDetail').value = request_detail;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>
