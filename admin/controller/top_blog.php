<?php
session_start();
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

if (isset($_GET['blog_id'])) {
    $blog_id = intval($_GET['blog_id']);
    
    // Mark the blog as top blog
    $sql = "UPDATE blogs SET is_top_blog = 1 WHERE blog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $blog_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Blog marked as Top Blog successfully!";
    } else {
        $_SESSION['message'] = "Error: Could not mark the blog as Top Blog.";
    }
    
    header('Location: ../manage_blogs.php');
    exit();
} else {
    $_SESSION['message'] = "Error: No blog ID provided.";
    header('Location: ../manage_blogs.php');
    exit();
}
?>
