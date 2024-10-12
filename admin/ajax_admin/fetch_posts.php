<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Assuming user role and Muni_ID are stored in session
$role = $_SESSION['role'];
$muni_id = $_SESSION['Muni_ID'];

// Initialize search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch posts based on the user's role, including municipality name, and applying the search filter
function getPosts($conn, $role, $muni_id, $search_query) {
    $search_term = '%' . $search_query . '%';
    if ($role === 'superadmin') {
        // Superadmin sees all pending posts
        $query = "SELECT p.*, m.MuniName FROM post p 
                  JOIN Municipalities m ON p.muni_id = m.Muni_ID 
                  WHERE p.status = 'pending'
                  AND (p.title LIKE ? OR p.place_type LIKE ? OR m.MuniName LIKE ?)";
    } else {
        // Admin sees only pending posts from their municipality
        $query = "SELECT p.*, m.MuniName FROM post p 
                  JOIN Municipalities m ON p.muni_id = m.Muni_ID 
                  WHERE p.muni_id = ? AND p.status = 'pending'
                  AND (p.title LIKE ? OR p.place_type LIKE ? OR m.MuniName LIKE ?)";
    }

    $stmt = $conn->prepare($query);

    if ($role === 'superadmin') {
        $stmt->bind_param('sss', $search_term, $search_term, $search_term);
    } else {
        $stmt->bind_param('ssss', $muni_id, $search_term, $search_term, $search_term);
    }

    $stmt->execute();
    return $stmt->get_result();
}

$posts = getPosts($conn, $role, $muni_id, $search_query);

$result_array = [];
while ($row = mysqli_fetch_assoc($posts)) {
    $result_array[] = $row;
}

header('Content-Type: application/json');
echo json_encode($result_array);
