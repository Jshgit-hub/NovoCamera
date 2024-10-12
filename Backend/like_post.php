<?php
session_start();
include 'connection.php';

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'];

// Check if the user has already liked this post
$query = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $postId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Insert the like into the database
    $query = "INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $postId, $userId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Post liked successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to like post']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'You have already liked this post']);
}
?>
