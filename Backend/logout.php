<?php
session_start();
include '../connection/connection.php'; // Include connection file if logging requires a database connection

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Log the logout action before destroying the session
if (isset($_SESSION['user_id']) && isset($_SESSION['Username'])) {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Logged out successfully.");
}

session_destroy();
?>
<script>
    window.onload = function() {
        alert('Log Out Successfully!');
        window.location = '../Login.php';
    };
</script>
