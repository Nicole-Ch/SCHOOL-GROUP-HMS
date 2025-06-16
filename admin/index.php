<?php

session_start();

$_SESSION['admin_id'] = 0;

header("Location: admin_dashboard.php"); 
exit();
?>