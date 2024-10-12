<?php
session_start();
include '../connection/connection.php';

// Log function to insert log into the database
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize input to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to check if the username exists
    $checkUsername = mysqli_query($conn, "SELECT * FROM users WHERE Username = '$username' LIMIT 1");
    $countUsername = mysqli_num_rows($checkUsername);

    if ($countUsername == 1) {
        $row = mysqli_fetch_assoc($checkUsername);
        $dbpassword = $row['Password'];
        $dbRole = $row['role'];
        $dbuser_id = $row['user_id']; // Fetch the user_id from the database

        // Verify the password
        $verifypass = password_verify($password, $dbpassword);

        if ($verifypass == true) {
            // Store user data in session
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $dbuser_id; // Correctly store the user_id in session
            $_SESSION['status'] = "Login Successful! Welcome back, $username.";
            $_SESSION['status_code'] = "success";

            // Log successful login
            logActivity($conn, $dbuser_id, $username, "Logged in successfully");

            // Redirect based on user role
            if ($dbRole == 1) {
                $_SESSION['role'] = $dbRole;
                header('Location: ../admin/admin.php');
            } else {
                header('Location: ../userfeed.php');
            }
            exit();
        } else {
            // Incorrect password
            $_SESSION['status'] = "Invalid username or password.";
            $_SESSION['status_code'] = "danger";

            // Log failed login attempt due to wrong password
            logActivity($conn, null, $username, "Failed login attempt: Incorrect password");

            header('Location: ../Login.php');
            exit();
        }
    } else {
        // Username not found
        $_SESSION['status'] = "Invalid username or password.";
        $_SESSION['status_code'] = "danger";

        // Log failed login attempt due to non-existent username
        logActivity($conn, null, $username, "Failed login attempt: Username not found");

        header('Location: ../Login.php');
        exit();
    }
}
?>
