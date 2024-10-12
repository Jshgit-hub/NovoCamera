<?php
// Start the session
session_start();

// Include the database connection file
include '../connection/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'User not logged in.'];
    header('Location: ../Login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $description = $_POST['description'];

    // Initialize the profile picture path
    $profile_picture_path = null;

    // Handle profile picture upload if provided
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Set a new unique file name
        $new_file_name = $user_id . '_' . uniqid() . '.' . $file_ext;
        $upload_dir = '../assets/images/profile_pictures/';

        // Make sure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move the uploaded file to the destination directory
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $profile_picture_path = $dest_path;
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Failed to upload profile picture.'];
            header('Location: ../profile-user.php');
            exit;
        }
    }

    // Update the user's profile in the database
    if ($profile_picture_path) {
        // Update all fields including the profile picture
        $query = "UPDATE users SET username = ?, Fullname = ?, Email = ?, Description = ?, profile_picture = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $username, $fullname, $email, $description, $profile_picture_path, $user_id);
    } else {
        // Update all fields except the profile picture
        $query = "UPDATE users SET username = ?, Fullname = ?, Email = ?, Description = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $username, $fullname, $email, $description, $user_id);
    }

    if ($stmt->execute()) {
        // Success message
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Profile updated successfully.'];
        header('Location: ../profile-user.php');
        exit;
    } else {
        // Failure message
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Failed to update profile.'];
        header('Location: ../profile-user.php');
        exit;
    }
} else {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Invalid request.'];
    header('Location: ../profile-user.php');
    exit;
}
