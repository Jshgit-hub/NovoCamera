<?php
session_start();
include '../connection/connection.php'; // Ensure this path is correct

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['blog_id'])) {
    $blog_id = intval($_POST['blog_id']); // Validate blog_id as an integer
    $username = $_SESSION['username']; // Use the logged-in user's username

    // Check if the blog belongs to the logged-in user and is archived
    $sql = "SELECT * FROM blogs WHERE blog_id = ? AND author = ? AND archived = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $blog_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['message'] = "Unauthorized action. You can't delete this blog.";
        header('Location: ../user_archived_blogs.php');
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete related notifications first to avoid foreign key conflicts
        $sql_delete_notifications = "DELETE FROM notifications WHERE blog_id = ?";
        $stmt_delete_notifications = $conn->prepare($sql_delete_notifications);
        $stmt_delete_notifications->bind_param("i", $blog_id);
        $stmt_delete_notifications->execute();

        // Permanently delete the archived blog post
        $sql_delete_blog = "DELETE FROM blogs WHERE blog_id = ? AND author = ? AND archived = 1";
        $stmt_delete_blog = $conn->prepare($sql_delete_blog);
        $stmt_delete_blog->bind_param("is", $blog_id, $username);
        $stmt_delete_blog->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['message'] = "Blog post permanently deleted!";
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Permanently deleted blog post ID $blog_id.");

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        $_SESSION['message'] = "Error: Could not delete blog post. Please try again.";
    }

    header('Location: ../archived_blogs.php'); // Redirect back to the archived blogs page
    exit();
}
?>
