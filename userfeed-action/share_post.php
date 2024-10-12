<?php
session_start();
include '../connection/connection.php';

if (isset($_SESSION['user_id']) && isset($_POST['post_id'])) {
    $user_id = $_SESSION['user_id']; // The user who shared the post
    $post_id = $_POST['post_id'];

    // Get the owner of the post
    $query = "SELECT user_id FROM post WHERE post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post_owner = $result->fetch_assoc()['user_id'];

    // Insert the share into the shares table
    $insertShareQuery = "INSERT INTO shares (user_id, post_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertShareQuery);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();

    // Create the notification message
    $message = "Someone shared your post.";

    // Insert a notification for the post owner
    $insertNotificationQuery = "INSERT INTO notifications (user_id, post_id, action, sender_id, message) VALUES (?, ?, 'share', ?, ?)";
    $stmt = $conn->prepare($insertNotificationQuery);
    $stmt->bind_param("iiis", $post_owner, $post_id, $user_id, $message);
    $stmt->execute();

    echo "shared";
}

