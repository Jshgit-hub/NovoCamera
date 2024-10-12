<?php
session_start();
include '../connection/connection.php';

if (isset($_POST['blog_id'])) {
    $blog_id = intval($_POST['blog_id']);
    $username = $_SESSION['username'];

    // Check if the blog belongs to the user
    $sql = "SELECT * FROM blogs WHERE blog_id = ? AND author = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $blog_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['message'] = "Unauthorized action. You can't restore this blog.";
        header('Location: ../archived_blogs.php');
        exit;
    }

    // Restore the blog (set archived = 0)
    $sql_restore = "UPDATE blogs SET archived = 0 WHERE blog_id = ? AND author = ?";
    $stmt_restore = $conn->prepare($sql_restore);
    $stmt_restore->bind_param("is", $blog_id, $username);

    if ($stmt_restore->execute()) {
        $_SESSION['message'] = "Blog post restored successfully!";
    } else {
        $_SESSION['message'] = "Error: Could not restore the blog post.";
    }

    header('Location: ../archived_blogs.php');
    exit;
}
?>
