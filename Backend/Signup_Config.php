<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/vendor/autoload.php';
include '../connection/connection.php';

// Function to send verification email
function sendVerificationEmail($email, $verification_code) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'novocameratour@gmail.com';
        $mail->Password = 'wacp wjfx esew sopp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('novocameratour@gmail.com', 'novocamera');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body    = "Your verification code is <b>$verification_code</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (isset($_POST['register'])) {
    // Input fields
    $username    = $_POST['username'];
    $fullname    = $_POST['Fname'];
    $email       = $_POST['email'];
    $password    = $_POST['password'];
    $conpassword = $_POST['conpassword'];
    $description = isset($_POST['Description']) ? $_POST['Description'] : null;

    // Validation rules
    $pattern_username   = "/^[a-zA-Z0-9_]{1,16}$/";
    $pattern_fullname   = "/^[a-zA-Z.\-\s]+$/";
    $pattern_email      = "/^[a-zA-Z\d_\|\-\•\@]+@[a-zA-Z0-9]+\.[a-zA-Z\. ]+$/";
    $pattern_password   = "/.{8,}/";

    // Initialize error array
    $errors = [];

    // Validate username
    if (!preg_match($pattern_username, $username)) {
        $errors['username'] = "Username should be alphanumeric and between 1-16 characters.";
    } else {
        // Check if username already exists
        $checkUsername = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' LIMIT 1");
        if (mysqli_num_rows($checkUsername) > 0) {
            $errors['username'] = "Username already exists.";
        }
    }

    // Validate fullname
    if (!preg_match($pattern_fullname, $fullname)) {
        $errors['fullname'] = "Fullname can only contain letters, periods, and hyphens.";
    }

    // Validate email
    if (!preg_match($pattern_email, $email)) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if email already exists
        $checkEmail = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = "Email is already registered.";
        }
    }

    // Validate password
    if (!preg_match($pattern_password, $password)) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    // Validate confirm password
    if ($password !== $conpassword) {
        $errors['conpassword'] = "Passwords do not match.";
    }

    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST; // Store old input to refill the form
        header('Location: ../Signup.php');
        exit();
    }

    // Proceed with the registration if no errors
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_code = rand(100000, 999999); // Generate a 6-digit verification code

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, fullname, email, password, description, verification_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $fullname, $email, $hashed_password, $description, $verification_code);

    if ($stmt->execute()) {
        // Send verification email
        if (sendVerificationEmail($email, $verification_code)) {
            $_SESSION['status'] = "Please check your email for the verification code.";
            $_SESSION['status_code'] = "success";
            header('Location: ../verify_email_reg.php');
            exit();
        } else {
            $_SESSION['status'] = "Unable to send verification email. Please try again.";
            $_SESSION['status_code'] = "danger";
        }
    } else {
        $_SESSION['status'] = "Registration failed. Please try again.";
        $_SESSION['status_code'] = "danger";
    }

    $stmt->close();
    header('Location: ../Signup.php');
    exit();
}
