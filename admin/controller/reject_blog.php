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
function createNotification($conn, $user_id, $message, $blog_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, blog_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $user_id, $blog_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    $blog_id = $_POST['blog_id'];
    $rejection_reason = $_POST['rejection_reason'];

    // Update blog status to 'rejected'
    $sql = "UPDATE blogs SET approval_status = 'rejected' WHERE blog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);

    if ($stmt->execute()) {
        // Fetch the user details for the blog
        $sql_user = "SELECT author, title FROM blogs WHERE blog_id = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $blog_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $blog = $result_user->fetch_assoc();

        if ($blog) {
            // Fetch the user ID of the blog author from the users table using the author (username)
            $sql_user_id = "SELECT user_id FROM users WHERE username = ?";
            $stmt_user_id = $conn->prepare($sql_user_id);
            $stmt_user_id->bind_param("s", $blog['author']);
            $stmt_user_id->execute();
            $result_user_id = $stmt_user_id->get_result();
            $user = $result_user_id->fetch_assoc();

            if ($user) {
                // Notify the blog author about rejection
                $user_id = $user['user_id']; // Get user_id from the users table
                $message = "Your blog post titled '" . htmlspecialchars($blog['title']) . "' has been rejected. Reason: " . htmlspecialchars($rejection_reason);
                createNotification($conn, $user_id, $message, $blog_id);
            }

            // Log the rejection action
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Rejected blog post ID $blog_id.");
        }

        $_SESSION['message'] = "Blog post rejected successfully and user notified!";
    } else {
        $_SESSION['message'] = "Error: Could not reject the blog post or it may already be rejected.";
    }

    header('Location: ../manage_blogs.php');
    exit;
}
?>

<!-- Sample HTML form for rejection -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ececec;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
            padding: 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Reject Blog Post</h2>
        <p>Select a reason for rejecting the blog post.</p>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="blog_id" value="<?php echo isset($_GET['blog_id']) ? htmlspecialchars($_GET['blog_id']) : ''; ?>">
        <div class="mb-3">
            <label for="rejection_reason" class="form-label">Select Reason for Rejection</label>
            <select class="form-select" id="rejection_reason" name="rejection_reason" required>
                <option value="">Choose a reason...</option>
                <option value="Inappropriate content">Inappropriate content</option>
                <option value="Not relevant">Not relevant</option>
                <option value="Duplicate submission">Duplicate submission</option>
                <option value="Poor quality">Poor quality</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" name="submit" class="btn btn-danger">Reject Blog</button>
            <a href="../manage_blogs.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
