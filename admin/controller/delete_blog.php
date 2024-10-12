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

if (isset($_GET['blog_id'])) {
    $blog_id = intval($_GET['blog_id']);

    // First, delete related notifications
    $sql_delete_notifications = "DELETE FROM notifications WHERE blog_id = ?";
    $stmt_delete_notifications = $conn->prepare($sql_delete_notifications);
    $stmt_delete_notifications->bind_param('i', $blog_id);
    $stmt_delete_notifications->execute();
    
    // Then delete the blog
    $sql = "DELETE FROM blogs WHERE blog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $blog_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Deleted Blog ID $blog_id successfully.");
        $_SESSION['message'] = "Blog deleted successfully!";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to delete Blog ID $blog_id: " . $stmt->error);
        $_SESSION['message'] = "Error: Could not delete the blog.";
    }
    
    header('Location: ../manage_blogs.php');
    exit();
} else {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Attempted to delete a blog without providing a Blog ID.");
    $_SESSION['message'] = "Error: No blog ID provided.";
    header('Location: ../manage_blogs.php');
    exit();
}
?>
