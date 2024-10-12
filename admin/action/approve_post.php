<?php
session_start();
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Include the functions file
include 'functions.php';

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    
    // Update the post status to "Approved"
    $query = "UPDATE post SET status='Approved' WHERE post_id='$post_id'";
    
    if (mysqli_query($conn, $query)) {
        // Get the user_id of the post creator
        $query = "SELECT user_id FROM post WHERE post_id='$post_id'";
        $result = mysqli_query($conn, $query);
        $post = mysqli_fetch_assoc($result);

        $user_id = $post['user_id'];
        $message = "Your post has been approved!";

        // Create a notification for the user
        createNotification($conn, $user_id, $post_id, $message);

        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Approved post ID $post_id and notified user ID $user_id.");
        $_SESSION['message'] = "Post approved and user notified.";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to approve post ID $post_id.");
        $_SESSION['message'] = "Failed to approve the post.";
    }
    
    header("Location: ../Manage-posts.php");
    exit();
}
?>
