<?php
session_start();
include 'connection/connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token and check expiry
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Token is valid, show the password reset form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Update the user's password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();

                // Delete the token
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();

                $_SESSION['status'] = "Password has been reset successfully.";
                $_SESSION['status_code'] = "success";
                header("Location: login.php"); // Redirect to login page
                exit();
            } else {
                $_SESSION['status'] = "Passwords do not match.";
                $_SESSION['status_code'] = "error";
            }
        }
    } else {
        $_SESSION['status'] = "Invalid or expired token.";
        $_SESSION['status_code'] = "error";
    }
} else {
    $_SESSION['status'] = "Invalid request.";
    $_SESSION['status_code'] = "error";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="reset-container" style="width: 400px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <div class="reset-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Reset Password</h2>
            </div>
            <?php if (isset($_SESSION['status'])): ?>
                <div class="alert alert-<?php echo $_SESSION['status_code']; ?>">
                    <?php echo $_SESSION['status']; ?>
                </div>
                <?php unset($_SESSION['status']); ?>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <input type="password" class="form-control form-control-lg bg-light fs-6" name="new_password" placeholder="New Password" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control form-control-lg bg-light fs-6" name="confirm_password" placeholder="Confirm Password" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-lg w-100 fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px;">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
