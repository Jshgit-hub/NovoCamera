<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['delete_post_id'])) {  // Change this to POST since the form uses POST method
    $post_id = intval($_POST['delete_post_id']);  // Get the post ID from the POST request

    // First, delete the related records in the notifications table
    $delete_notifications = "DELETE FROM notifications WHERE post_id = ?";
    $stmt_notifications = $conn->prepare($delete_notifications);
    $stmt_notifications->bind_param('i', $post_id);
    $stmt_notifications->execute();
    $stmt_notifications->close();

    // Now, delete the post itself
    $delete_post = "DELETE FROM post WHERE post_id = ?";
    $stmt_post = $conn->prepare($delete_post);
    $stmt_post->bind_param('i', $post_id);

    if ($stmt_post->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Deleted Post ID $post_id successfully.");
        $_SESSION['message'] = "Post deleted successfully!";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to delete Post ID $post_id: " . $stmt_post->error);
        $_SESSION['message'] = "Error: Could not delete the post.";
    }

    header('Location: ../userfeed.php');
    exit();
} else {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Attempted to delete a post without providing a Post ID.");
    $_SESSION['message'] = "Error: No post ID provided.";
    header('Location: ../user-post-details.php');
    exit();
}
?>
