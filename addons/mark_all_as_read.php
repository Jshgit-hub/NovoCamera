<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);

    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    $stmt->close();
    exit();
}
?>
