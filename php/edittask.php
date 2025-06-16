<?php
session_start();
require_once '../config.php';

$id = "";
$request_detail = "";
$room_location = "";
$status = "";
$submitted_at = "";

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show data of the task
    if (!isset($_GET["id"])) {
        header("Location: createTask.php");
        exit();
    }
    
    $id = $_GET["id"];
    // Read from database using $conn
    $sql = "SELECT * FROM maintenance_requests WHERE id=$id";
    $result = $conn->query($sql);
    if (!$result) {
        $errorMessage = "Query Error: " . $conn->error;
        header("Location: createTask.php");
        exit();
    }
    
    $row = $result->fetch_assoc();
    if (!$row) {
        header("Location: createTask.php");
        exit();
    }
    
    // Populate variables with data from the database
    $id = $row["id"];
    $request_detail = $row["request_detail"];
    $room_location = $row["room_location"];
    $status = $row["status"];
    $submitted_at = $row["submitted_at"];
} else {
    // Update data of task (POST)
    // Make sure your form has a hidden input for 'id'
    $id = isset($_POST["id"]) ? $_POST["id"] : "";
    $request_detail = $_POST["request_detail"];
    $room_location = $conn->real_escape_string($_POST["room_location"]);
    $status = $_POST["status"];
    $submitted_at = $_POST["submitted_at"];

    do {
        if (empty($id) || empty($request_detail) || empty($room_location) || empty($status) || empty($submitted_at)) {
            $errorMessage = "Please input every stated field";
            break;
        }
        
        // Build UPDATE query
        $sql = "UPDATE maintenance_requests SET request_detail='$request_detail', room_location='$room_location', status='$status', submitted_at='$submitted_at' WHERE id='$id'";
        $result = $conn->query($sql);
        
        if (!$result) {
            $errorMessage = "Invalid Query: " . $conn->error;
            break;
        }
        
        $successMessage = "Task updated Successfully";
        header("Location: createTask.php");
        exit();
    } while (false);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit New Task</title>

    <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- Bootstrap Icons & Remix Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  
  <link rel="stylesheet" href="../css/maintenance.css">

</head>
<body>
     <!-- CREATE TASK FORM -->
     <div class="container my-5">
        <h2>New Task</h2>

     <?php
       if(!empty($errorMessage)){
        echo "
        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
        <strong>$errorMessage</strong>
        <button type='button' class='btn btn-close' data-bs-dismiss='alert'></button>
      </div>
        ";
       }
     
     ?>

        <form method="post" action="edittask.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">




          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Request Detail</label>
            <div class="col-sm-6">
              <input type="text" class="form-control" name="request_detail" placeholder="Enter task detail" value="<?php echo $request_detail;?>">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Room Location</label>
            <div class="col-sm-6">
              <input type="text" class="form-control" name="room_location" placeholder="Enter room location" value="<?php echo $room_location;?>">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-6">
              <input type="text" class="form-control" name="status" placeholder="Enter status" value="<?php echo $status;?>">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Created At</label>
            <div class="col-sm-6">
              <input type="datetime-local" class="form-control" name="submitted_at" placeholder="Enter created date" value="<?php echo $submitted_at;?>">
            </div>
          </div>


    <!--SUCCESS MESSAGE-->
    <?php
          if(!empty($successMessage)){
            echo "
            <div class='row mb-3'>
            <div class='alert alert-success alert-dismissible fade show' role='alert'>
            <strong>$successMessage</strong>
            <button type='button' class='btn btn-close' data-bs-dismiss='alert'></button>
          </div>
            </div>
            
            ";
           } 
    ?>

          <div class="row mb-3">
            <div class="offset-sm-3 col-sm-3 d-grid">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <div class="col-sm-3 d-grid">
              <a href="createTask.php" class="btn btn-outline-primary" role="button">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>