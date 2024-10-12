<?php
include '../connection/connection.php';

// Retrieve filter data from POST request
$place_type = isset($_POST['place_type']) ? $_POST['place_type'] : 'all';
$municipality = isset($_POST['municipality']) ? $_POST['municipality'] : 'all';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Build the SQL query to filter gallery items
$sql = "SELECT * FROM gallery_images WHERE 1";

// Apply place type filter if selected
if ($place_type !== 'all') {
    $sql .= " AND place_type = '" . $conn->real_escape_string($place_type) . "'";
}

// Apply municipality filter if selected
if ($municipality !== 'all') {
    $sql .= " AND municipality = '" . $conn->real_escape_string($municipality) . "'";
}

// Apply year filter if provided
if (!empty($year)) {
    $sql .= " AND year = '" . $conn->real_escape_string($year) . "'";
}

// Execute the query
$result = $conn->query($sql);

// Check if any results were found
if ($result->num_rows > 0) {
    // Loop through the result and return each gallery item in HTML format
    while ($row = $result->fetch_assoc()) {
        echo '
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="uploads/gallery/' . htmlspecialchars($row['image_path']) . '" class="card-img-top gallery-image" alt="' . htmlspecialchars($row['title']) . '">
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>
                        <p class="card-text">' . htmlspecialchars($row['description']) . '</p>
                    </div>
                </div>
            </div>
        ';
    }
} else {
    // No results found
    echo '<p>No gallery items match your filters.</p>';
}

$conn->close();
?>
