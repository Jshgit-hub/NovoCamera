<?php
session_start();
include 'connection/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php'; // Ensure PHPMailer autoload is included

// Verify the email with the code
if (isset($_POST['verify'])) {
    $verification_code = $_POST['verification_code'];

    // Retrieve the user based on the verification code
    $stmt = $conn->prepare("SELECT email FROM users WHERE verification_code = ? AND is_verified = 0");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email']; // Retrieve the email from the database

        // Now mark the user as verified
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE verification_code = ?");
        $stmt->bind_param("s", $verification_code);
        if ($stmt->execute()) {
            $_SESSION['status'] = "Email verified successfully. You can now log in.";
            $_SESSION['status_code'] = "success";
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['status'] = "Verification failed. Please try again.";
            $_SESSION['status_code'] = "danger";
        }
    } else {
        $_SESSION['status'] = "Invalid verification code or your account is already verified.";
        $_SESSION['status_code'] = "danger";
    }
}

// Resend the verification code
if (isset($_POST['resend'])) {
    $verification_code = $_POST['verification_code'];

    // Retrieve the user based on the verification code
    $stmt = $conn->prepare("SELECT email FROM users WHERE verification_code = ? AND is_verified = 0");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email']; // Retrieve the email from the database

        // Generate a new verification code
        $new_verification_code = rand(100000, 999999);

        // Update the new verification code in the database
        $stmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_verification_code, $email);
        $stmt->execute();

        // Resend the verification email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'novocameratour@gmail.com';
            $mail->Password = 'wacp wjfx esew sopp'; // Your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('novocameratour@gmail.com', 'NovoCamera');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Resend Verification Code';
            $mail->Body = "Your new verification code is <b>$new_verification_code</b>";

            $mail->send();
            $_SESSION['status'] = "A new verification code has been sent to your email.";
            $_SESSION['status_code'] = "success";
        } catch (Exception $e) {
            $_SESSION['status'] = "Failed to resend the verification code. Please try again.";
            $_SESSION['status_code'] = "error";
        }
    } else {
        $_SESSION['status'] = "Invalid verification code.";
        $_SESSION['status_code'] = "danger";
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
            <form action="verify_email_reg.php" method="post">
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
