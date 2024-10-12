<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/vendor/autoload.php';
include '../connection/connection.php';

// Function to send reset email
function sendResetEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'novocameratour@gmail.com'; // Your Gmail address
        $mail->Password = 'wacp wjfx esew sopp'; // Your Gmail password (use app-specific password if 2FA enabled)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('novocameratour@gmail.com', 'novocamera');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "Click the link to reset your password: <a href='http://localhost/reset_password.php?token=$token'>Reset Password</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Validate email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(50)); // Generate a secure token
            $expires = time() + 1800; // 30 minutes from now

            // Update database with reset token and expiration
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE Email = ?");
            $stmt->bind_param("sis", $token, $expires, $email);
            $stmt->execute();

            // Send the reset email
            if (sendResetEmail($email, $token)) {
                $_SESSION['status'] = "Password reset email sent. Please check your inbox.";
                $_SESSION['status_code'] = "success";
            } else {
                $_SESSION['status'] = "Failed to send reset email. Please try again.";
                $_SESSION['status_code'] = "danger";
            }
        } else {
            $_SESSION['status'] = "Email address not found.";
            $_SESSION['status_code'] = "danger";
        }
    } else {
        $_SESSION['status'] = "Invalid email address.";
        $_SESSION['status_code'] = "danger";
    }

    header('Location: ../forgot_password.php');
    exit();
}
