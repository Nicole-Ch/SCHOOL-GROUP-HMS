<?php
  session_start(); #used to start a  session

  #array to store errors encountered during login or registration
  $errors = [
       'login' => $_SESSION['login_error'] ?? '', #if no error then an empty string
       'register' => $_SESSION['register_error'] ?? ''

  ];

  $activeForm = $_SESSION['active_form'] ?? 'login'; #determines which form is active

  session_unset(); #removes all existing session variables

 #used to display the error that is encountered on the form
  function showError($error){
       return !empty($error) ? "<p class='error-message'>$error</p>" : '';
       //If the $error is not empty , it returns an HTML paragraph  that contains the error message else an empty string
  }

  #used to check if formname matches the active form
  function isActiveForm($formName , $activeForm){
       return $formName === $activeForm ? 'active' : '';
  }


?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="css/login.css">
    
</head>
<body>
<div class="container">
       <div class="formdisplay <?=isActiveForm('login',$activeForm);?>">
              <form action="validatelogin.php" method="post">
                     <h2>Login</h2>

                     <?=showError($errors['login']);?> <!--Displays Login errors-->

                     <input type="email" name="email" placeholder="user@gmail.com" required>
                     <input type="password" name="password" placeholder="password" required>


                     
                     <button type="submit" name="loginStudent">Login as a Student</button>
                     
                     <button type="submit" name="login">Login</button>
                     <p>Dont have an Account? <a href="register.php">Register here</a></p>
                     <hr>
                     <div class="link-container">
                            <a id="backhome" href="index.html">Back to Home</a>
                     </div>
              </form>
       </div>
</div>

    



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
 integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

