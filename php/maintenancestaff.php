<?php

// maintenance.php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'maintenancestaff') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';
// Ensures that the most recent maintenance requests appear at the top.
$query = "SELECT id, request_detail, room_location, status, submitted_at FROM maintenance_requests ORDER BY submitted_at DESC";
$result = $conn->query($query);

// IN PROGRESS TASKS COUNT
$query_active = "SELECT COUNT(*) as active_count FROM maintenance_requests WHERE status = 'in progress'";
$result_active = $conn->query($query_active);
$active_count = $result_active ? $result_active->fetch_assoc()['active_count'] : 0;


// COMPLETED TASKS COUNT
$query_completed = "SELECT COUNT(*) as completed_count FROM maintenance_requests WHERE status = 'completed'";
$result_completed = $conn->query($query_completed);
$completed_count = $result_completed ? $result_completed->fetch_assoc()['completed_count'] : 0;

// AVAILABLE STAFF COUNT
$query_staff = "SELECT COUNT(*) as staff_count FROM users WHERE role = 'maintenancestaff'";
$result_staff = $conn->query($query_staff);
$staff_count = $result_staff ? $result_staff->fetch_assoc()['staff_count'] : 0;





?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

        <!--BOOTSTRAP ICONS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link
    href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
    rel="stylesheet">

  <link rel="stylesheet" href="../css/maintenance.css">
<style>
  /*DESIGNING THE METRIC DISPLAY*/
  .card-metric {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
      border-radius: 15px;
      background: linear-gradient(135deg,rgb(178, 186, 195) 0%, #e9ecef 100%);
      overflow: hidden;
      position: relative;
  }
  
  .card-metric:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  
  .metric-icon {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      opacity: 0.8;
  }

</style>

</head>
<body>
<?php include 'header.php'; ?>
  <div class="d-flex">  <!-- Flexbox container for sidebar and main content -->
    <!-- LEFT SIDEBAR -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content Area -->
    <div class="main-content">
     
      
      <!-- Key Metrics Cards -->
      <div class="row mt-5 pt-5">
        <div class="col-md-3 ">
          <div class="card card-metric ">
            <div class="card-body text-center">
            <i class="bi bi-hourglass-split metric-icon text-warning"></i>
            <h3><?= htmlspecialchars($active_count) ?></h3>
              <p>Active Tasks</p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card card-metric">
            <div class="card-body text-center">
            <i class="bi bi-check-circle metric-icon text-success"></i>
               <h3><?= htmlspecialchars($completed_count) ?></h3>
              <p>Completed Tasks</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card card-metric">
            <div class="card-body text-center">
            <i class="bi bi-people metric-icon text-info"></i>
            <h3><?= htmlspecialchars($staff_count) ?></h3>
              <p>Team Members</p>
            </div>
          </div>
        </div>
        
      
      <!-- Recent Tasks Table -->
      <h2 class="mt-4">Recent Maintenance Requests</h2>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Task ID</th>
              <th>Description</th>
              <th>Room Location</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            
            <?php if($result && $result->num_rows > 0): ?>
              <?php while($row = $result->fetch_assoc()): ?> <!--Fetches the next available row from the $result object as an associative array-->
                
                <tr>
                  <td><?=htmlspecialchars($row['id'])?></td>
                  <td><?=htmlspecialchars($row['request_detail'])?></td>
                  <td><?=htmlspecialchars($row['room_location'])?></td>
                  <td><?=htmlspecialchars($row['status'])?></td>
                  <td><?=htmlspecialchars($row['submitted_at'])?></td>
                  <td>
                    <!-- Button to mark a request as Resolved -->
                    <form action="mark-resolved.php" method="POST" class="d-inline">
                      <input type="hidden" name="request_id" value="<?=htmlspecialchars($row['id'])?>">
                      <input type="hidden" name="status" value="completed">
                      <button type="submit" class="btn btn-sm btn-success">Mark Resolved</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center">No maintenance requests found.</td>
                </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
