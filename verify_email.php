<?php
session_start();
include 'connection/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php'; // Ensure PHPMailer autoload is included

if (isset($_POST['reset_password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Check if username and email match and ensure the role is not 'admin' or 'superadmin'
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = 'user'");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Generate a reset token
        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token in the database
        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $token, $expires);
        $stmt->execute();

        // Send reset email
        $reset_link = "http://192.168.100.16:8080/user_reset_password.php?token=$token";

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

            // Redirect to the waiting page
            header("Location: password_reset_waiting.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['status'] = "Failed to send the reset link. Please try again.";
            $_SESSION['status_code'] = "error";

            // Redirect to the waiting page
            header("Location: password_reset_waiting.php");
            exit();
        }
    } else {
        $_SESSION['status'] = "Username and email do not match, or you do not have permission to reset your password.";
        $_SESSION['status_code'] = "error";
        // Redirect back to the forgot password page with error message
        header("Location: forgot_password.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">

    <!-- Display Alert Message -->
    <?php if (isset($_SESSION['status']) && isset($_SESSION['status_code'])): ?>
        <div class="alert alert-<?php echo $_SESSION['status_code']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['status']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['status'], $_SESSION['status_code']); ?>
    <?php endif; ?>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="verify-container" style="max-width: 450px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div class="verify-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Verify Your Email</h2>
                <p style="color: #666;">Enter the verification code sent to your email</p>
            </div>
            <form action="" method="post">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg bg-light fs-6" name="verification_code" placeholder="Verification Code" required style="border-radius: 10px;">
                </div>
                <div id="resend-container">
                    <button type="submit" name="verify" class="btn btn-lg w-100 fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px; margin-top: 10px;">Verify</button>
                    <button type="submit" name="resend" id="resend-btn" class="btn btn-lg w-100 fs-6" style="background-color: #f1f1f1; color: #666; border: none; border-radius: 10px; padding: 10px; margin-top: 10px;" disabled>Resend Code</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const resendBtn = document.getElementById('resend-btn');
            let resendTimeout;

            // Enable resend button after 2 minutes (120000 ms)
            function enableResend() {
                resendBtn.disabled = false;
                resendBtn.textContent = "Resend Code";
            }

            // Set the timeout to enable the resend button after 2 minutes
            resendTimeout = setTimeout(enableResend, 120000);

            // Handle resend button click
            resendBtn.addEventListener('click', function() {
                resendBtn.disabled = true;
                resendBtn.textContent = "Please wait...";
                clearTimeout(resendTimeout); // Clear previous timeout
                resendTimeout = setTimeout(enableResend, 120000); // Set new timeout
            });
        });
    </script>
</body>

</html>