<?php 
session_start();

include 'connection/connection.php';

if (isset($_POST['register'])) {
    $username      = $_POST['username'];
    $fullname      = $_POST['Fname'];
    $email         = $_POST['email'];
    $password      = $_POST['password'];
    $conpassword   = $_POST['conpassword'];
    $description   = isset($_POST['Description']) ? $_POST['Description'] : null;  // Handle the description field

    // Validation patterns
    $pattern_username    = "/^[a-zA-Z0-9_]{1,16}$/";
    $pattern_fullname    = "/^[a-zA-Z.\-\s]+$/";
    $pattern_email       = "/^[a-zA-Z\d_\|\-\•\@]+@[a-zA-Z0-9]+\.[a-zA-Z\. ]+$/";
    $pattern_password    = "/.{8,}/";

    // Validation results
    $result_username     = preg_match($pattern_username, $username);
    $result_fullname     = preg_match($pattern_fullname, $fullname);
    $result_email        = preg_match($pattern_email, $email);
    $result_password     = preg_match($pattern_password, $password);
    $result_conpassword  = preg_match($pattern_password, $conpassword);

    if ($password == $conpassword) {
        if ($result_username == 1) {
            if ($result_fullname == 1) {
                if ($result_email == 1) {
                    if ($result_password == 1) {
                        $password = password_hash($password, PASSWORD_DEFAULT);

                        // Check if username already exists
                        $checkUsername = mysqli_query($conn, "SELECT * FROM users WHERE Username = '$username' LIMIT 1");
                        $count = mysqli_num_rows($checkUsername);

                        if ($count == 1) {
                            $_SESSION['status'] = "Username already exists.";
                            $_SESSION['status_code'] = "danger";  
                        } else {
                            // Insert data into the database
                            $result = mysqli_query($conn, "INSERT INTO users (Username, Fullname, Email, Password, Description, Date_Created, role, Muni_ID) VALUES ('$username', '$fullname', '$email', '$password', '$description', NOW(), 'superadmin', 8)");

                            if (!$result) {
                                die("Unable to save");
                            } else {
                                $_SESSION['status'] = "Account Created Successfully";
                                $_SESSION['status_code'] = "success";
                                header('Location: adsignup.php');
                                exit();
                            } 
                        }
                    } else {
                        $_SESSION['status'] = "Invalid Password";
                        $_SESSION['status_code'] = "danger";
                    }
                } else {
                    $_SESSION['status'] = "Invalid Email";
                    $_SESSION['status_code'] = "danger";
                }
            } else {
                $_SESSION['status'] = "Invalid Fullname";
                $_SESSION['status_code'] = "danger";
            }
        } else {
            $_SESSION['status'] = "Invalid Username";
            $_SESSION['status_code'] = "danger";
        }
    } else {
        $_SESSION['status'] = "Passwords do not match";
        $_SESSION['status_code'] = "danger";
    }

    header('Location: adsignup.php');
    exit();
}

?>