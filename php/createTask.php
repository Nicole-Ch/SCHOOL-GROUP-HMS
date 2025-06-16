<?php
// createTask.php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'maintenancestaff') {
    header("Location: ../login.php");
    exit();
}
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Tasks</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- Bootstrap Icons & Remix Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  
  <link rel="stylesheet" href="../css/maintenance.css">
 
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <div class="container mx-5">
      <br>
      <h2>Create New Tasks</h2>
      <a href="addnewtask.php" class="btn btn-primary" role="button">New Task</a>
      <br><br>

     
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Request Detail</th>
            <th>Room Location</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Read the rows from the database
            $sql = "SELECT * FROM maintenance_requests ORDER BY submitted_at DESC";
            $result = $conn->query($sql); //executes the query

            if (!$result) {
                die("Invalid query: " . $conn->error);
            }
            while ($row = $result->fetch_assoc()) {
                // Set background color based on the status
        $bgColor = "";
        if ($row['status'] == 'pending') {
            $bgColor = 'style="background-color: #ffcccc;"'; // light red
        } elseif ($row['status'] == 'in progress') {
            $bgColor = 'style="background-color: #fff0b3;"'; // light yellow
        } elseif ($row['status'] == 'completed') {
            $bgColor = 'style="background-color: #ccffcc;"'; // light green
        }

                echo "
                    <tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td>" . htmlspecialchars($row['request_detail']) . "</td>
                        <td>" . htmlspecialchars($row['room_location']) . "</td>
                        <td $bgColor>" . htmlspecialchars($row['status']) . "</td>
                        <td>" . htmlspecialchars($row['submitted_at']) . "</td>
                        <td>
                            <a href='edittask.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-primary btn-sm'>Edit</a>
                            <a href='deletetask.php?id=" . htmlspecialchars($row['id']) . "' 
                    class='btn btn-danger btn-sm' 
                    onclick='return confirm(\"Are you sure you want to delete this record?\")'>
                    Delete
                  </a>

                        </td>
                    </tr>
                ";
            }
          ?>
        </tbody>
      </table>

     

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
