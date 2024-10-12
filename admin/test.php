<?php
session_start();
include '../connection/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/vendor/autoload.php'; // Ensure PHPMailer autoload is included

if (isset($_POST['reset_password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Check if username and email match
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
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
        $reset_link = "http://192.168.100.16:8080/admin/admin_reset_password.php?token=$token";

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

            // Redirect to the password reset sent page
            header("Location: password_reset_sent.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['status'] = "Failed to send the reset link. Please try again.";
            $_SESSION['status_code'] = "error";

            // Redirect to the password reset sent page
            header("Location: password_reset_sent.php");
            exit();
        }
    } else {
        $_SESSION['status'] = "Username and email do not match.";
        $_SESSION['status_code'] = "error";
        // Redirect back to the same page with error message
        header("Location: admin_forgot_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="forgot-password-container" style="width: 400px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <div class="forgot-password-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Forgot Password</h2>
                <p style="color: #666;">Enter your username and email to reset your password</p>
                <?php
                if (isset($_SESSION['status'])) {
                    $status = $_SESSION['status'];
                    $status_code = $_SESSION['status_code'];
                    echo "<div class='alert text-danger alert-$status_code'>$status</div>";
                    unset($_SESSION['status']);
                    unset($_SESSION['status_code']);
                }
                ?>
            </div>
            <form action="admin_forgot_password.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg bg-light fs-6" name="username" placeholder="Username" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control form-control-lg bg-light fs-6" name="email" placeholder="Email Address" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <button type="submit" name="reset_password" class="btn btn-lg w-100 fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px;">Send Reset Link</button>
                </div>
                <div class="login-link" style="text-align: center; margin-top: 1rem;">
                    <small><a href="admin_login.php" style="color: #379777; text-decoration: none; font-weight: 500;">Back to Login</a></small>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
