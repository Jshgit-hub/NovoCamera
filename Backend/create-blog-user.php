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

// Handle form submission
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_SESSION['username']; // Get the current logged-in user's username
    $blog_type = $_POST['blog_type']; // Selected blog type
    $municipality_id = $_POST['municipality'];
    $image_display_type = $_POST['image_display_type'];
    $approval_status = 'pending'; // Default approval status for user-submitted blogs
    $uploaded_by = 'user'; // Set this to 'user' when the post is submitted by a user
    $message = '';

    // Image upload handling
    $uploaded_images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_directory = '../assets/images/blogs/user/'; // Different folder for user blogs
        foreach ($_FILES['images']['name'] as $key => $image_name) {
            $image_tmp_name = $_FILES['images']['tmp_name'][$key];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_name_new = uniqid() . '.' . $image_ext;
            $image_path = $upload_directory . $image_name_new;

            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $uploaded_images[] = $image_name_new;
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Uploaded image $image_name_new for blog post.");
            } else {
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to upload image $image_name_new for blog post.");
            }
        }
    }

    // Convert the array of image names to a comma-separated string
    $images_string = implode(',', $uploaded_images);

    // Insert blog post into the database (without bind_param)
    $sql = "INSERT INTO blogs (title, content, author, blog_type, municipality_id, approval_status, image_display_type, image_url, uploaded_by, created_at)
            VALUES ('$title', '$content', '$author', '$blog_type', '$municipality_id', '$approval_status', '$image_display_type', '$images_string', '$uploaded_by', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        $message = 'Blog post submitted successfully and is awaiting approval!';
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Created blog post titled '$title'.");
    } else {
        $message = 'Error: Could not submit blog post: ' . $conn->error;
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to create blog post titled '$title'.");
    }

    $_SESSION['message'] = $message;
    header('Location: ../userfeed.php');
    exit;
}
?>
