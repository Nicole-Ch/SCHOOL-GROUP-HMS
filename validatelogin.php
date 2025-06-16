<?php
session_start();
require_once 'config.php';

if (isset($_POST['login']) || isset($_POST['loginStudent'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // When "Login" button is pressed (for admin/staff)
    if (isset($_POST['login'])) {
        $checkUser = $conn->query("SELECT id, email, password, role, firstname FROM users WHERE email = '$email'");
        
        if ($checkUser && $checkUser->num_rows > 0) {
            $user = $checkUser->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['id']    = $user['id'];
                $_SESSION['name']  = $user['firstname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role']  = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin/admin_dashboard.php");
                    exit();
                } elseif ($user['role'] === 'maintenancestaff') {
                    header("Location: php/maintenancestaff.php");
                    exit();
                } else {
                    $_SESSION['login_error'] = 'Invalid role detected!';
                    header("Location: login.php");
                    exit();
                }
            }
        }

        $_SESSION['login_error'] = 'Incorrect email or password for regular login!';
        header("Location: login.php");
        exit();
    }
    
    // When "Login as a Student" button is pressed
    if (isset($_POST['loginStudent'])) {
        $checkStudent = $conn->query("SELECT id, first_name, second_name, email, password, status FROM checkins WHERE email = '$email'");

        if ($checkStudent && $checkStudent->num_rows > 0) {
            $student = $checkStudent->fetch_assoc();

            if ($student['status'] !== 'checked in') {
                $_SESSION['login_error'] = 'You are not currently checked in!';
                header("Location: login.php");
                exit();
            }

            if (password_verify($password, $student['password'])) {
                $_SESSION['id']    = $student['id'];
                $_SESSION['name']  = $student['first_name'] . ' ' . $student['second_name'];
                $_SESSION['email'] = $student['email'];
                $_SESSION['role']  = 'student';

                // Redirect to checkedin.php since that's where students should go
                header("Location: students/checkedin.php");
                exit();
            }
        }

        $_SESSION['login_error'] = 'Incorrect email or password for student login!';
        header("Location: login.php");
        exit();
    }

}

// If no login button was pressed (or unexpected access)
$_SESSION['login_error'] = 'Unexpected error during login!';
header("Location: login.php");
exit();
?>
