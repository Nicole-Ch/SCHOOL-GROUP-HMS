<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Umojahostel_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users
$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

$users = [];
if ($result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Delete user
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
    $stmt->close();
}

// Edit user (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $userId = $_POST['edit_user_id'];
    $firstName = $_POST['firstname'];
    $secondName = $_POST['secondname'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (empty($firstName) || empty($email)) {
        echo "First name and email are required.";
        exit;
    }

    $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $firstName, $secondName, $email, $role, $userId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch user for editing
$user_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql_edit = "SELECT * FROM users WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows == 1) {
        $user_to_edit = $result_edit->fetch_assoc();
    }
    $stmt_edit->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Users</title>
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
    margin: 7% auto;  
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
        <h2>Umoja Hostel</h2>
       
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
        <h1>Users</h1>
        <div class="search-bar">
            Search: <input type="text" id="search-input" onkeyup="searchTable()">
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Second Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id']; ?></td>
                        <td><?= $user['firstname']; ?></td>
                        <td><?= $user['lastname']; ?></td>
                        <td><?= $user['email']; ?></td>
                        <td><?= $user['role']; ?></td>
                        <td class="action-buttons">
                            <button onclick="openEditModal(<?= $user['id']; ?>)" class="edit-btn">Edit</button>
                            <button onclick="confirmDelete(<?= $user['id']; ?>)" class="delete-btn">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="modal" style="<?= $user_to_edit ? 'display:block;' : 'display:none;' ?>">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none';">&times;</span>
        <h2>Edit User</h2>
        <form method="POST" action="admin_dashboard.php">
            <input type="hidden" id="edit_user_id" name="edit_user_id" value="<?= $user_to_edit ? $user_to_edit['id'] : '' ?>">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?= $user_to_edit['firstname'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="secondName">Second Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?= $user_to_edit['lastname'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= $user_to_edit['email'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <input type="text" id="role" name="role" value="<?= $user_to_edit['role'] ?? '' ?>">
            </div>
            <button type="submit" name="edit_user" class="save-btn">Save</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(userId) {
        // Fetch user data and populate the modal
        fetch('admin_dashboard.php?edit_id=' + userId)
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const htmlDoc = parser.parseFromString(data, 'text/html');
                const user = JSON.parse(htmlDoc.getElementById('user_data').textContent);

                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('firstname').value = user.firstname;
                document.getElementById('lastname').value = user.lastname;
                document.getElementById('email').value = user.email;
                document.getElementById('role').value = user.role;

                document.getElementById('editModal').style.display = 'block';
            });
    }

    function confirmDelete(userId) {
        if (confirm("Are you sure you want to delete this user?")) {
            window.location.href = "admin_dashboard.php?delete_id=" + userId;
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

    function searchTable() {
        let input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("search-input");
        filter = input.value.toUpperCase();
        table = document.getElementById("users-table-body");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            let found = false;
            for (let j = 0; j < tr[i].cells.length; j++) {
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

<?php if ($user_to_edit): ?>
    <div id="user_data" style="display: none;">
        <?= json_encode($user_to_edit); ?>
    </div>
<?php endif; ?>
</body>
</html>