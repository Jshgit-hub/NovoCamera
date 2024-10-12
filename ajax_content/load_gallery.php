<?php
include '../connection/connection.php';

// Get filters from request
$place_type = $_GET['place_type'] ?? 'all';
$municipality = $_GET['municipality'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build the base query
$sql = "SELECT p.id, p.title, p.image_path, p.latitude, p.longitude, p.created_at, m.MuniName 
        FROM gallery_images p
        JOIN municipalities m ON p.Muni_ID = m.Muni_ID
        WHERE 1";

// Add filter for place type if selected
if ($place_type !== 'all') {
    $sql .= " AND p.place_type = '" . $conn->real_escape_string($place_type) . "'";
}

// Add filter for municipality if selected
if ($municipality !== 'all') {
    $sql .= " AND p.Muni_ID = " . (int)$municipality;
}

// Add search filter if a search term is provided
if (!empty($search)) {
    $sql .= " AND p.title LIKE '%" . $conn->real_escape_string($search) . "%'";
}

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
