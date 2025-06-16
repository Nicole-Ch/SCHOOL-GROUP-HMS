<!-- sidebar.php -->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current page's filename
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
      <div class="profile">
        <!-- IMAGE -->
        <img src="../images/staff.png" alt="Profile Image">
        <h5><?=$_SESSION['name'];?></h5>
        <p>Maintenance Staff</p>
      </div>
      <nav class="nav flex-column">
        <a class="nav-link <?= $currentPage == 'maintenancestaff.php' ? 'active' : '' ?>" href="maintenancestaff.php">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
         
        <a class="nav-link <?= $currentPage == 'createTask.php' ? 'active' : '' ?>" href="createTask.php"> <i class="bi bi-pencil-square me-2"></i>Create Task</a>
       
        <a class="nav-link <?= $currentPage == 'checkins.php' ? 'active' : '' ?>" href="checkins.php">
    <i class="bi bi-person-check me-2"></i>Checkins
</a>

<a class="nav-link <?= $currentPage == 'checkout.php' ? 'active' : '' ?>" href="checkout.php">
    <i class="bi bi-box-arrow-right me-2"></i>Checkouts
</a>


       
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
    </div>
