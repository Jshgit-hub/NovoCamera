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

if (isset($_POST['submit'])) {
    $blog_id = $_POST['blog_id']; // Blog ID for editing
    $title = $_POST['title'];
    $content = $_POST['content'];
    $blog_type = $_POST['blog_type'];
    $municipality_id = $_POST['municipality'];
    $image_display_type = $_POST['image_display_type'];
    $message = '';

    // Handle image upload if new images are uploaded
    $uploaded_images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_directory = '../assets/images/blogs/user/';
        foreach ($_FILES['images']['name'] as $key => $image_name) {
            $image_tmp_name = $_FILES['images']['tmp_name'][$key];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_name_new = uniqid() . '.' . $image_ext;
            $image_path = $upload_directory . $image_name_new;

            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $uploaded_images[] = $image_name_new;
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Uploaded new image $image_name_new for blog post.");
            } else {
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to upload image $image_name_new for blog post.");
            }
        }
    }

    // Get existing images from the database if no new images are uploaded
    $sql = "SELECT image_url FROM blogs WHERE blog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();

    if (empty($uploaded_images)) {
        $images_string = $blog['image_url']; // Use existing images if no new images are uploaded
    } else {
        $images_string = implode(',', $uploaded_images); // New images
    }

    // Update blog post
    $sql_update = "UPDATE blogs SET title = ?, content = ?, blog_type = ?, municipality_id = ?, image_display_type = ?, image_url = ?, updated_at = NOW() WHERE blog_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssissi", $title, $content, $blog_type, $municipality_id, $image_display_type, $images_string, $blog_id);

    if ($stmt_update->execute()) {
        $message = 'Blog post updated successfully!';
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Updated blog post titled '$title'.");
    } else {
        $message = 'Error: Could not update blog post.';
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to update blog post titled '$title'.");
    }

    $_SESSION['message'] = $message;
    header('Location: ../userfeed.php');
    exit;
}
?>
