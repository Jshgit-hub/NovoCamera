<?php
// Function to create a notification
function createNotification($conn, $user_id, $post_id, $message) {
    $query = "INSERT INTO notifications (user_id, post_id, message, is_read) VALUES (?, ?, ?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $user_id, $post_id, $message);
    return $stmt->execute();
}


