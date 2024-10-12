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

$username = $_SESSION['username']; // Use the logged-in user's username

if (isset($_POST['empty_archive'])) {

    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all archived blog IDs for the logged-in user
        $sql_get_archived_blogs = "SELECT blog_id FROM blogs WHERE author = ? AND archived = 1";
        $stmt_get_archived_blogs = $conn->prepare($sql_get_archived_blogs);
        $stmt_get_archived_blogs->bind_param("s", $username);
        $stmt_get_archived_blogs->execute();
        $result_archived_blogs = $stmt_get_archived_blogs->get_result();

        if ($result_archived_blogs->num_rows > 0) {
            // Loop through all archived blogs and delete related notifications first
            while ($row = $result_archived_blogs->fetch_assoc()) {
                $blog_id = $row['blog_id'];

                // Delete related notifications
                $sql_delete_notifications = "DELETE FROM notifications WHERE blog_id = ?";
                $stmt_delete_notifications = $conn->prepare($sql_delete_notifications);
                $stmt_delete_notifications->bind_param("i", $blog_id);
                $stmt_delete_notifications->execute();
            }

            // After deleting all notifications, delete the archived blogs
            $sql_delete_archived_blogs = "DELETE FROM blogs WHERE author = ? AND archived = 1";
            $stmt_delete_archived_blogs = $conn->prepare($sql_delete_archived_blogs);
            $stmt_delete_archived_blogs->bind_param("s", $username);
            $stmt_delete_archived_blogs->execute();

            // Commit transaction
            $conn->commit();

            $_SESSION['message'] = "All archived blogs permanently deleted!";
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Emptied archived blogs for user $username.");

        } else {
            $_SESSION['message'] = "No archived blogs to delete.";
        }

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        $_SESSION['message'] = "Error: Could not empty archived blogs. Please try again.";
    }

    header('Location: ../archived_blogs.php');
    exit();
}
?>
