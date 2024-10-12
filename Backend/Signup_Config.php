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
    $username    = $_POST['username'];
    $fullname    = $_POST['Fname'];
    $email       = $_POST['email'];
    $password    = $_POST['password'];
    $conpassword = $_POST['conpassword'];
    $description = isset($_POST['Description']) ? $_POST['Description'] : null;

    // Validation patterns
    $pattern_username   = "/^[a-zA-Z0-9_]{1,16}$/";
    $pattern_fullname   = "/^[a-zA-Z.\-\s]+$/";
    $pattern_email      = "/^[a-zA-Z\d_\|\-\•\@]+@[a-zA-Z0-9]+\.[a-zA-Z\. ]+$/";
    $pattern_password   = "/.{8,}/";

    // Validation results
    $result_username    = preg_match($pattern_username, $username);
    $result_fullname    = preg_match($pattern_fullname, $fullname);
    $result_email       = preg_match($pattern_email, $email);
    $result_password    = preg_match($pattern_password, $password);
    $result_conpassword = preg_match($pattern_password, $conpassword);

    if ($password == $conpassword) {
        if ($result_username == 1 && $result_fullname == 1 && $result_email == 1 && $result_password == 1) {
            $password = password_hash($password, PASSWORD_DEFAULT);

            // Check if username already exists
            $checkUsername = mysqli_query($conn, "SELECT * FROM users WHERE Username = '$username' LIMIT 1");
            $count = mysqli_num_rows($checkUsername);

            if ($count == 1) {
                $_SESSION['status'] = "Username already exists.";
                $_SESSION['status_code'] = "danger";  
            } else {
                // Generate verification code
                $verification_code = rand(100000, 999999);

                // Insert the user data into the database
                $stmt = $conn->prepare("INSERT INTO users (Username, Fullname, Email, Password, Description, verification_code) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $fullname, $email, $password, $description, $verification_code);

                if ($stmt->execute()) {
                    // Send verification email
                    if (sendVerificationEmail($email, $verification_code)) {
                        $_SESSION['status'] = " Please check your email for the verification code.";
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
            }
        } else {
            $_SESSION['status'] = "Invalid form data.";
            $_SESSION['status_code'] = "danger";
        }
    } else {
        $_SESSION['status'] = "Passwords do not match";
        $_SESSION['status_code'] = "danger";
    }

    header('Location: ../Signup.php');
    exit();
}
?>
