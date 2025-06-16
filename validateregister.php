<?php
   session_start();
   require_once 'config.php';

   // Function to validate names (letters, spaces, apostrophes, hyphens, and accented characters)
   function validate_name($name) {
       // expression for validating names checking if it contains only valid characters
       return preg_match("/^[\p{L}\s'-]+$/u", $name);  // \p{L} allows all Unicode letters
   }

   if (isset($_POST['Register'])) {
       // Capture form data and sanitize inputs
       $firstname = htmlspecialchars($_POST['firstname']);
       $lastname = htmlspecialchars($_POST['lastname']);
       $email = htmlspecialchars($_POST['email']);
       $password = $_POST['password']; 
       $role = $_POST['role'];

       // Validate firstname and lastname using the custom validation function
       if (!validate_name($firstname)) {
           $_SESSION['register_error'] = 'First name contains invalid characters!';
           $_SESSION['active_form'] = 'register';
           header("location:register.php");
           exit();
       }

       if (!validate_name($lastname)) {
           $_SESSION['register_error'] = 'Last name contains invalid characters!';
           $_SESSION['active_form'] = 'register';
           header("location:register.php");
           exit();
       }

       // Prepare the SQL statement to prevent SQL injection
       $checkemail_query = $conn->prepare("SELECT email FROM users WHERE email = ?");
       $checkemail_query->bind_param("s", $email); // 's' means string type
       $checkemail_query->execute();
       $result = $checkemail_query->get_result(); //result is checked to see if any record with the same email exists.

       if ($result->num_rows > 0) {
           // Email already exists
           $_SESSION['register_error'] = 'Email is already Registered!';
           $_SESSION['active_form'] = 'register';
           header("location:register.php");
           exit(); // Make sure to exit after the redirect
       } else {
           // Hash the password before inserting
           $hashed_password = password_hash($password, PASSWORD_DEFAULT);

           // Insert new user into the database using prepared statements
           $insert_query = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)");
           $insert_query->bind_param("sssss", $firstname, $lastname, $email, $hashed_password, $role);
           //  binds the parameters to the SQL query and tells the database what the parameters are.

           if ($insert_query->execute()) {
               // Redirect to login.php if registration is successful
               header("location:login.php");
               exit();
           } else {
               $_SESSION['register_error'] = 'An error occurred. Please try again.';
               $_SESSION['active_form'] = 'register';
               header("location:register.php");
               exit();
           }
       }
   }
?>
