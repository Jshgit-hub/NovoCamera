<?php
session_start();
if (isset($_SESSION['status'])) {
    $status = $_SESSION['status'];
    $status_code = $_SESSION['status_code'];
    echo "<script>alert('$status');</script>";
    unset($_SESSION['status']); // Clear the alert message
    unset($_SESSION['status_code']); // Clear the status code
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="login-container" style="width: 400px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <div class="login-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Welcome Back, Admin</h2>
                <p style="color: #666;">Please login to your admin account</p>
            </div>
            <form action="../Backend/admin/login-admin-config.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg bg-light fs-6" name="username" placeholder="Username" required style="border-radius: 10px;">
                </div>
                <div class="mb-1">
                    <input type="password" class="form-control form-control-lg bg-light fs-6" name="password" placeholder="Password" required style="border-radius: 10px;">
                </div>
                <div class="forgot-password" style="text-align: right; margin-top: 10px; margin-bottom: 1rem;">
                    <a href="admin_forgot_password.php" style="color: #379777; text-decoration: none;">Forgot Password?</a>
                </div>
                <div class="mb-3">
                    <button type="submit" name="login" class="btn btn-lg w-100 fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px;">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
