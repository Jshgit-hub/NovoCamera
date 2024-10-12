<?php
include("../connection/connection.php");

if (isset($_GET['district_id']) && isset($_GET['current_muni_id'])) {
    $district_id = intval($_GET['district_id']);
    $current_muni_id = intval($_GET['current_muni_id']);

    $sql = "SELECT Muni_ID, MuniName, MuniPicture FROM municipalities WHERE District_ID = ? AND Muni_ID != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $district_id, $current_muni_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $municipalities = [];
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }

    echo json_encode($municipalities);
} else {
    echo json_encode([]);
}
?>
