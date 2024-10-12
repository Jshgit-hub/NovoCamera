<?php
include("../connection/connection.php");

if (isset($_GET['Muni_ID'])) {
    $muni_id = intval($_GET['Muni_ID']);

    // Fetch tourism places within this municipality
    $sql_places = "SELECT * FROM places WHERE Muni_ID = ?";
    $stmt_places = $conn->prepare($sql_places);
    $stmt_places->bind_param('i', $muni_id);
    $stmt_places->execute();
    $result_places = $stmt_places->get_result();
    $places = $result_places->fetch_all(MYSQLI_ASSOC);

    if ($places) {
        foreach ($places as $place) {
            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../place-details.php?Place_ID=" . htmlspecialchars($place['Place_ID']) . "' class='card-link'>";
            echo "<div class='card where-to-go-card border-0 text-white' style='border-radius: 15px; overflow: hidden; width: 100%; height: 350px;'>";
            echo "<img src='" . htmlspecialchars($place['PlacePicture']) . "' class='card-img' style='height: 100%; object-fit: cover; border-radius: 15px;' alt='" . htmlspecialchars($place['PlaceName']) . "'>";
            echo "<div class='card-img-overlay where-to-go-overlay d-flex flex-column justify-content-end p-3' style='background: rgba(0, 0, 0, 0.4);'>";
            echo "<h5 class='card-title mb-0 text-center text-warning'>" . htmlspecialchars($place['PlaceName']) . "</h5>";
            echo "<p class='card-text text-center text-light'><i class='fas fa-map-marker-alt'></i> " . htmlspecialchars($place['PlaceLocation']) . "</p>";
            echo "</div></div></a></div>";
        }
    } else {
        echo "<div class='col-12'><p class='text-center text-light'>No tourism places found in this municipality.</p></div>";
    }
} else {
    echo "<div class='col-12'><p class='text-center text-light'>Invalid request.</p></div>";
}
