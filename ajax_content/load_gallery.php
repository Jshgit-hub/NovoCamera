<?php
include '../connection/connection.php';

// Get filters from request
$municipality = $_GET['municipality'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$year = $_GET['year'] ?? 'all';
$sort_order = $_GET['sort_order'] ?? 'DESC'; // Default sorting by newest

// Build the base query
$sql = "SELECT p.id, p.Place_ID, p.post_id, p.title, p.image_path, p.latitude, p.longitude, p.created_at, m.MuniName 
        FROM gallery_images p
        JOIN municipalities m ON p.Muni_ID = m.Muni_ID
        WHERE 1";

// Add filter for municipality if selected
if ($municipality !== 'all') {
    $sql .= " AND p.Muni_ID = " . (int)$municipality;
}

// Add date range filter if provided
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND p.created_at BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "'";
}

// Add year filter if selected
if ($year !== 'all') {
    $sql .= " AND YEAR(p.created_at) = " . (int)$year;
}

// Add sorting by date (newest or oldest)
$sql .= " ORDER BY p.created_at " . $sort_order;

$result = $conn->query($sql);

$places = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $places[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($places);
$conn->close();
?>
