<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the admin ID from the URL
$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete the admin record
$query = "DELETE FROM users WHERE user_id = $admin_id";

if (mysqli_query($conn, $query)) {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Deleted Admin ID $admin_id successfully.");
    header("Location: ../Manage-admin.php?msg=deleted"); // Redirect after deletion
    exit();
} else {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to delete Admin ID $admin_id: " . mysqli_error($conn));
    echo "Error deleting record: " . mysqli_error($conn);
}
?>
