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
  <title>Checkin</title>
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
      <h2>Student Room Checkins</h2>
      
      <br><br>

     
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Room Number</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Read the rows from the database
           // Read the rows from the database
           $sql = "SELECT * FROM checkins WHERE status = 'checked in' ORDER BY checkin_datetime DESC";

            $result = $conn->query($sql);

            if (!$result) {
                die("Invalid query: " . $conn->error);
            }
            while ($row = $result->fetch_assoc()) {
               

                echo "
                    <tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td>" . htmlspecialchars($row['first_name']) . "</td>
                        <td>" . htmlspecialchars($row['second_name']) . "</td>
                        <td>" . htmlspecialchars($row['room_number']) . "</td>
                        <td>" . htmlspecialchars($row['checkin_datetime']) . "</td>
                        <td>" . htmlspecialchars($row['checkout_datetime']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                       
                        <td>
                           <a href='checkout.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-success btn-sm'>Checkout</a>

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
