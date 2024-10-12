<?php
session_start();

include '../connection/connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your existing code...


if (!isset($_GET['token'])) {
    $_SESSION['status'] = "Invalid token.";
    $_SESSION['status_code'] = "danger";
    header('Location: error.php'); // Redirect to an error page
    exit();
}

$token = $_GET['token'];

// Fetch user details using the token
$query = "SELECT * FROM users WHERE reset_token = ? AND invitation_status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Redirect to the complete profile page with the token
    header("Location:complete_profile.php?token=$token");
    exit();
} else {
    $_SESSION['status'] = "Invalid or expired token.";
    $_SESSION['status_code'] = "danger";
    header('Location: error.php'); // Redirect to an error page
    exit();
}
