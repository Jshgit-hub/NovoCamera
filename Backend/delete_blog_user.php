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

    // Check if the blog belongs to the logged-in user (using author as the username)
    $sql = "SELECT * FROM blogs WHERE blog_id = ? AND author = ? AND archived = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $blog_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['message'] = "Unauthorized action. You can't archive this blog.";
        header('Location: ../user_blogs.php');
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Mark the blog as archived instead of deleting it
        $sql_archive_blog = "UPDATE blogs SET archived = 1 WHERE blog_id = ? AND author = ?";
        $stmt_archive_blog = $conn->prepare($sql_archive_blog);
        $stmt_archive_blog->bind_param("is", $blog_id, $username);
        $stmt_archive_blog->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['message'] = "Blog post archived successfully!";
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Archived blog post ID $blog_id.");

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        $_SESSION['message'] = "Error: Could not archive blog post. Please try again.";
    }

    header('Location: ../user_blogs.php');
    exit;
}
?>
