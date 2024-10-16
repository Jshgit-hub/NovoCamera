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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="login-container" style="width: 400px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <div class="login-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Welcome Back</h2>
                <p style="color: #666;">Please login to your account</p>
            </div>
            <form action="Backend/Login_config.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg bg-light fs-6" name="username" placeholder="Username" required style="border-radius: 10px;">
                </div>
                <div class="mb-3 position-relative">
                    <input type="password" class="form-control form-control-lg bg-light fs-6" name="password" id="password" placeholder="Password" required style="border-radius: 10px;">
                    <i class="bi bi-eye-slash-fill position-absolute top-50 end-0 translate-middle-y pe-3" id="togglePassword" style="cursor: pointer;"></i> <!-- Eye Icon -->
                </div>
                <div class="forgot-password" style="text-align: right; margin-top: 10px; margin-bottom: 1rem;">
                    <a href="forgot_password.php" style="color: #379777; text-decoration: none;">Forgot Password?</a>
                </div>
                <div class="mb-3">
                    <button type="submit" name="login" class="btn btn-lg w-100 fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px;">Login</button>
                </div>
                <div class="signup-link" style="text-align: center; margin-top: 1rem;">
                    <small>Don't have an account? <a href="Signup.php" style="color: #379777; text-decoration: none; font-weight: 500;">Sign Up</a></small>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for toggling the password visibility -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function (e) {
            // Toggle the type attribute of the password input field
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle the eye/eye-slash icon
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash-fill');
        });
    </script>
</body>

</html>
