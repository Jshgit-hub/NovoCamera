<?php
session_start();
include '../../connection/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // Ensure PHPMailer autoload is included

if (isset($_POST['reset_password'])) {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Generate a reset token
        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token in database
        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $token, $expires);
        $stmt->execute();

        // Send reset email
        $reset_link = "http://yourdomain.com/admin_reset_password.php?token=$token";

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'novocameratour@gmail.com'; // Your email
            $mail->Password = 'wacp wjfx esew sopp'; // Your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('novocameratour@gmail.com', 'NovoCamera');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
            <p>Hello,</p>
            <p>You have requested a password reset. Please click the link below to reset your password:</p>
            <p><a href='$reset_link'>$reset_link</a></p>
            <p>If you did not request this password reset, please ignore this email.</p>
            <p>Thank you!</p>";

            $mail->send();
            $_SESSION['status'] = "Password reset link has been sent to your email.";
            $_SESSION['status_code'] = "success";
        } catch (Exception $e) {
            $_SESSION['status'] = "Failed to send the reset link. Please try again.";
            $_SESSION['status_code'] = "error";
        }
    } else {
        $_SESSION['status'] = "Email not found.";
        $_SESSION['status_code'] = "error";
    }

    header("Location: admin_forgot_password.php");
    exit();
}
?>
