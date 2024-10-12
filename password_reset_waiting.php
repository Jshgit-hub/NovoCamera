<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Your Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="waiting-container" style="width: 400px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px; text-align: center;">
            <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Check Your Email</h2>
            <p style="color: #666;">We've sent a password reset link to your email. Please check your inbox.</p>
            <div class="mt-3">
                <a href="login.php" class="btn btn-primary">Back to Login</a>
            </div>
        </div>
    </div>
</body>

</html>
