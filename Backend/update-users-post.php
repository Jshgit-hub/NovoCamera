<?php

$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update_post'])) {
    // Retrieve and sanitize form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description1 = mysqli_real_escape_string($conn, $_POST['description1']);
    $placeType = mysqli_real_escape_string($conn, $_POST['place_type']);
    $Muni_ID = mysqli_real_escape_string($conn, $_POST['location']); // Using Muni_ID instead of location
    $post_id = $_GET['post_id'];

    // Fetch the old image URL from the database
    $query = "SELECT image_url FROM post WHERE post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $oldImage = $post['image_url'];

    // Handling image upload
    $newImage = $_FILES['postImage']['name'];
    if ($newImage) {
        $temp_path = $_FILES['postImage']['tmp_name'];
        $uploadDir = '../assets/images/userUpload/';
        $newImageFileName = time() . '_' . basename($newImage);
        $destination = $uploadDir . $newImageFileName;

        // Validate and move the new image file
        if (move_uploaded_file($temp_path, $destination)) {
            // Delete the old image file if it exists and is not the default image
            if ($oldImage && file_exists($oldImage)) {
                unlink($oldImage);
            }

            // Update the image path in the database along with other fields
            $query = "UPDATE post SET title = ?, description1 = ?, place_type = ?, Muni_ID = ?, image_url = ? WHERE post_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssisi", $title, $description1,  $placeType, $Muni_ID, $destination, $post_id);
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Updated post ID $post_id with new image.");
        } else {
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to upload new image for post ID $post_id.");
            echo "<script>alert('Failed to upload new image.');</script>";
            exit;
        }
    } else {
        // Update other details without changing the image
        $query = "UPDATE post SET title = ?, description1 = ?, place_type = ?, Muni_ID = ? WHERE post_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssisi", $title, $description1,  $placeType, $Muni_ID, $post_id);
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Updated post ID $post_id without changing the image.");
    }

    // Execute the update query
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Post ID $post_id updated successfully.");
        echo "<script>alert('Post updated successfully!'); window.location.href='user-post-details.php?post_id=$post_id';</script>";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to update post ID $post_id.");
        echo "<script>alert('Failed to update post.');</script>";
    }
}
?>
