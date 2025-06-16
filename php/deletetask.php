<?php
session_start();
require_once '../config.php';

     if(isset($_GET["id"])){
        $id = $_GET["id"];

    $sql  ="DELETE FROM maintenance_requests WHERE id = $id";
    $conn->query($sql);

     }

     header("location: createTask.php");
     exit;

?>