<?php
 session_start();
 session_unset(); #deletes all user login info
 session_destroy(); #terminates active session

 header("location:../login.php");
 exit();


?>