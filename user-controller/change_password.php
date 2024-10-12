<?php
session_start();
include '../connection/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Fetch the current password from the database
    $query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (password_verify($current_password, $hashed_password)) {
        // Check if the new password and confirm new password match
        if ($new_password === $confirm_new_password) {
            // Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_hashed_password, $user_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Password successfully updated!";
            } else {
                $_SESSION['error_message'] = "Error updating password.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "New password and confirm new password do not match.";
        }
    } else {
        $_SESSION['error_message'] = "Current password is incorrect.";
    }

    $conn->close();

    // Redirect back to the profile page with an active tab
    header("Location: ../profile-user.php?tab=security");
    exit();
}
?>
