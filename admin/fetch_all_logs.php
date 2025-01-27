<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Ensure the user is logged in and is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Variables for filtering and searching
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Query to fetch all logs, including user role and any filters
$query = "SELECT logs.*, users.role FROM logs 
          LEFT JOIN users ON logs.user_id = users.user_id 
          WHERE 1";

// Add role filter if set
if ($role_filter !== '') {
    $query .= " AND users.role = '$role_filter'";
}

// Add search term filter if set
if ($search_term !== '') {
    $query .= " AND logs.username LIKE '%$search_term%'";
}

// Order by latest timestamp
$query .= " ORDER BY logs.timestamp DESC";

$result = mysqli_query($conn, $query);

$logs = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
}

// Return the logs as JSON
header('Content-Type: application/json');
echo json_encode($logs);

mysqli_close($conn);
?>
