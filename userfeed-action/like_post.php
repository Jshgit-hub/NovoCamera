<?php
session_start();
include '../connection/connection.php';

if (isset($_SESSION['user_id']) && isset($_POST['post_id'])) {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];

    // Check if the user has already liked the post
    $checkLikeQuery = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($checkLikeQuery);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Unlike the post
        $deleteLikeQuery = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
        $stmt = $conn->prepare($deleteLikeQuery);
        $stmt->bind_param("ii", $user_id, $post_id);
        if ($stmt->execute()) {
            // Remove the like notification for the post owner
            $deleteNotificationQuery = "DELETE FROM notifications WHERE user_id = ? AND post_id = ? AND action = 'like'";
            $stmt = $conn->prepare($deleteNotificationQuery);
            $stmt->bind_param("ii", $user_id, $post_id);
            $stmt->execute();

            echo "unliked";
        }
    } else {
        // Like the post
        $insertLikeQuery = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertLikeQuery);
        $stmt->bind_param("ii", $user_id, $post_id);
        if ($stmt->execute()) {
            // Get the post owner to determine whether to notify
            $query = "SELECT user_id FROM post WHERE post_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $post_owner = $result->fetch_assoc()['user_id'];

            // Only create a notification if the liker is not the post owner
            if ($user_id != $post_owner) {
                // Create a notification for the post owner
                $message = "Someone liked your post.";
                $insertNotificationQuery = "INSERT INTO notifications (user_id, post_id, action, sender_id, message) VALUES (?, ?, 'like', ?, ?)";
                $stmt = $conn->prepare($insertNotificationQuery);
                $stmt->bind_param("iiis", $post_owner, $post_id, $user_id, $message);
                $stmt->execute();
            }

            echo "liked";
        }
    }
}

