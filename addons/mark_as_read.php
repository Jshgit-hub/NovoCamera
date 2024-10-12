<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (isset($_POST['notification_id']) && isset($_SESSION['user_id'])) {
    $notification_id = intval($_POST['notification_id']);
    $user_id = intval($_SESSION['user_id']);

    // Check the current state before updating
    $checkQuery = "SELECT is_read FROM notifications WHERE notification_id = ? AND user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $notification_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $is_read_before = $row['is_read'];

    // Update the notification as read
    $query = "UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $notification_id, $user_id);

    if ($stmt->execute()) {
        // Check the state after updating
        $stmt->execute();
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('ii', $notification_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $is_read_after = $row['is_read'];

        echo json_encode(['status' => 'success', 'is_read_before' => $is_read_before, 'is_read_after' => $is_read_after]);
    } else {
        echo json_encode(['status' => 'error', 'error' => mysqli_error($conn)]);
    }
    $stmt->close();
    exit();
}
?>
