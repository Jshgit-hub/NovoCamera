<?php
session_start();
include 'functions.php';
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['id'])) {
    $post_id = (int)$_GET['id']; // Cast to integer for security
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        
        // Fetch the user_id of the post owner
        $query = "SELECT user_id FROM post WHERE post_id = $post_id";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $post = mysqli_fetch_assoc($result);
            $owner_id = (int)$post['user_id']; // The user_id of the post owner

            // Update the post status to "Rejected" with a reason
            $query = "UPDATE post SET status='Rejected', reason_for_rejection='$reason' WHERE post_id=$post_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Your post has been rejected. Reason: $reason";

                // Create a notification for the post owner
                createNotification($conn, $owner_id, $post_id, $message);

                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Rejected post ID $post_id with reason: $reason.");
                $_SESSION['message'] = "Post rejected and user notified.";
            } else {
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to reject post ID $post_id.");
                $_SESSION['message'] = "Failed to reject the post.";
            }
        } else {
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Post ID $post_id not found or user not found.");
            $_SESSION['message'] = "Post not found or user not found.";
        }
        
        header("Location: ../Manage-posts.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reject Post</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-danger text-white text-center">
                        <h4 class="mb-0">Reject Post</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-center text-muted">Please select a reason for rejecting this post. The reason will be sent to the user.</p>
                        <form method="POST" action="reject_post.php?id=<?php echo $post_id; ?>">
                            <div class="form-floating mb-3">
                                <select id="reason" name="reason" class="form-control" required>
                                    <option value="" disabled selected>Select a reason for rejection</option>
                                    <option value="Inappropriate content">Inappropriate content</option>
                                    <option value="Violation of guidelines">Violation of guidelines</option>
                                    <option value="Spam">Spam</option>
                                    <option value="Misleading information">Misleading information</option>
                                    <option value="Copyright infringement">Copyright infringement</option>
                                    <!-- Add more predefined reasons here -->
                                </select>
                                <label for="reason">Reason for rejection</label>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-danger btn-lg">Submit</button>
                                <a href="../Manage-posts.php" class="btn btn-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional for Bootstrap components functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
