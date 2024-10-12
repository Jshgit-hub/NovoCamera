<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Sent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="text-center" style="width: 400px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <?php
            if (isset($_SESSION['status'])) {
                echo '<div class="alert alert-' . $_SESSION['status_code'] . '">';
                echo $_SESSION['status'];
                echo '</div>';
                unset($_SESSION['status']);
                unset($_SESSION['status_code']);
            } else {
                echo '<div class="alert alert-info">We are processing your request. Please wait...</div>';
            }
            ?>
            <p class="mt-3">Please check your email for the password reset link. If you do not see it, please check your spam/junk folder.</p>
            <a href="admin_login.php" class="btn btn-primary mt-3">Back to Login</a>
        </div>
    </div>
</body>

</html>
