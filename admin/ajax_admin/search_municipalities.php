<?php
include '../../connection/connection.php';

// Initialize search query
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = trim($_POST['search']);
}

// Fetch municipalities based on search query
$municipalities = [];
if (!empty($search_query)) {
    $sql = "SELECT * FROM municipalities WHERE MuniName LIKE ? OR Mayor LIKE ? OR Location LIKE ? ORDER BY LOWER(MuniName) ASC";
    $stmt = $conn->prepare($sql);
    $search_term = '%' . $search_query . '%';
    $stmt->bind_param('sss', $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $municipalities = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $sql = "SELECT * FROM municipalities ORDER BY LOWER(MuniName) ASC";
    $result = $conn->query($sql);
    $municipalities = $result->fetch_all(MYSQLI_ASSOC);
}

// Generate the HTML for the table rows
if (!empty($municipalities)) {
    foreach ($municipalities as $municipality) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($municipality['MuniName']) . '</td>';
        echo '<td>' . htmlspecialchars($municipality['Population']) . '</td>';
        echo '<td>' . htmlspecialchars($municipality['Area']) . '</td>';
        echo '<td>' . htmlspecialchars($municipality['Mayor']) . '</td>';
        echo '<td>';
        echo '<a href="controller/edit_municipality.php?Muni_ID=' . $municipality['Muni_ID'] . '" class="btn btn-sm btn-primary">Edit</a>';
        echo '<form action="manage_municipality.php" method="POST" style="display:inline;">';
        echo '<input type="hidden" name="muni_id" value="' . $municipality['Muni_ID'] . '">';
        echo '<button type="submit" name="delete_municipality" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this municipality?\');">Delete</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">No municipalities found.</td></tr>';
}
?>
