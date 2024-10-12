<?php
session_start();

// Include the database connection file
include 'connection/connection.php';

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the input data
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $description = trim($_POST['description']);

    // Validate the inputs (basic validation)
    if (empty($username) || empty($fullname) || empty($email)) {
        echo "Please fill in all required fields.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Prepare the SQL statement to update the user's profile
    $query = "UPDATE users SET username = ?, Fullname = ?, Email = ?, Description = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("ssssi", $username, $fullname, $email, $description, $user_id);
        
        if ($stmt->execute()) {
            logActivity($conn, $user_id, $username, "Profile updated successfully.");
            // Redirect to the profile page with a success message
            $_SESSION['profile_update_success'] = "Profile updated successfully!";
            header("Location: profile-user.php");
            exit;
        } else {
            logActivity($conn, $user_id, $username, "Failed to update profile: " . $stmt->error);
            echo "Error updating profile: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        logActivity($conn, $user_id, $username, "Query preparation failed: " . $conn->error);
        echo "Query preparation failed: " . $conn->error;
    }
}

$conn->close();

