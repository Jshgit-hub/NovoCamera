<?php
session_start();
require '../../connection/connection.php'; // Ensure this path is correct

// Function to log activity
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Function to create a notification for the blog owner
function createNotification($conn, $user_id, $message, $blog_id, $post_id = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, post_id, blog_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iiis", $user_id, $post_id, $blog_id, $message);
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
}

if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];

    // Fetch blog details to get the author and title
    $sql = "SELECT author, title FROM blogs WHERE blog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();

    if ($blog) {
        // Fetch the user ID of the blog author from the users table using the author (username)
        $sql_user = "SELECT user_id FROM users WHERE username = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("s", $blog['author']);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $user = $result_user->fetch_assoc();

        if ($user) {
            // Update blog status to published
            $sql_update = "UPDATE blogs SET status = 'published', approval_status = 'approved' WHERE blog_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $blog_id);

            if ($stmt_update->execute()) {
                // Log activity for admin approval
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Approved blog post ID $blog_id.");

                // Notify the blog author about approval
                $user_id = $user['user_id']; // Get user_id from the users table
                $message = "Your blog post titled '" . htmlspecialchars($blog['title']) . "' has been published!";
                
                // Create notification with NULL for post_id
                createNotification($conn, $user_id, $message, $blog_id, null);

                $_SESSION['message'] = "Blog post published successfully and user notified!";
            } else {
                $_SESSION['message'] = "Error: Could not publish the blog post.";
            }
        } else {
            $_SESSION['message'] = "Error: Blog author not found in the users table.";
        }
    } else {
        $_SESSION['message'] = "Error: Blog post not found.";
    }

    header('Location: ../manage_blogs.php');
    exit;
}
?>
