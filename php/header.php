<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="../css/maintenance.css">
<div class="header p-3 fixed-top" style="background-color: #23272b; left: 250px; right: 0; z-index: 1000;">
  <div class="container d-flex justify-content-between align-items-center">
    <h2 class="mb-0" style="color: #fff;">Maintenance Dashboard</h2>

    <!-- Notifications Dropdown -->
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle position-relative" type="button" id="taskNotifications" data-bs-toggle="dropdown">
        <i class="ri-notification-3-fill fs-3" style="color: #fff;"></i>
        <span id="taskCountBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle">0</span>
      </button>

      <ul class="dropdown-menu dropdown-menu-end" id="taskList">
        <li class="dropdown-item">No pending tasks</li>
      </ul>
    </div>
  </div>
</div>

<!-- JavaScript to Fetch Pending & In-Progress Tasks -->
<script src="../js/notifications.js"></script>
  

