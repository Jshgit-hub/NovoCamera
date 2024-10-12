<?php
include 'connection/connection.php';

// Query to fetch 6 categories
$sql = "SELECT category_id, name AS activity_name, description, image_url AS image_path FROM categories LIMIT 6";
$result = $conn->query($sql);

// Prepare an array to store categories
$categories = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();
?>
