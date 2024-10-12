<?php
session_start();
require '../connection/connection.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch blogs based on search query or fetch all if no search query is provided
if (!empty($search_query)) {
    $sql = "SELECT * FROM blogs WHERE title LIKE ? OR author LIKE ? ORDER BY views DESC, created_at DESC";
    $stmt = $conn->prepare($sql);
    $search_term = '%' . $search_query . '%';
    $stmt->bind_param('ss', $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $sql = "SELECT * FROM blogs ORDER BY views DESC, created_at DESC";
    $result = $conn->query($sql);
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($blogs);
?>
