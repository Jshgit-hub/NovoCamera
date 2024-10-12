<?php
session_start();
include '../../connection/connection.php'; // Ensure this path is correct

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $blog_type = $_POST['blog_type'];
    $municipality_id = $_POST['municipality'];
    $status = $_POST['status'];
    $image_display_type = $_POST['image_display_type'];
    $message = '';

    // Image upload
    $uploaded_images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_directory = '../../assets/images/blogs/admin/';
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

    // Save blog post to the database
    $sql = "INSERT INTO blogs (title, content, author, blog_type, municipality_id, status, image_display_type, image_url, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"; // Removed the extra placeholder.

    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$title, $content, $author, $blog_type, $municipality_id, $status, $image_display_type, $images_string])) {
        $message = 'Blog post created successfully!';
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Created blog post titled '$title'.");
    } else {
        $message = 'Error: Could not create blog post.';
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to create blog post titled '$title'.");
    }

    $_SESSION['message'] = $message;
    header('Location: ../create_blog.php');
    exit;
}
?>
