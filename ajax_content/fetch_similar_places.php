<?php
include("../connection/connection.php");

// Get the 'PlaceType' and 'exclude_place_id' from the AJAX request
if (isset($_GET['PlaceType']) && isset($_GET['exclude_place_id'])) {
    $place_type = htmlspecialchars($_GET['PlaceType']);
    $exclude_place_id = htmlspecialchars($_GET['exclude_place_id']);

    // Prepare SQL query
    $sql_places = "SELECT p.Place_ID, p.PlaceName, p.PlacePicture, p.Latitude, p.Longitude, p.PlaceLocation, m.MuniName, p.PlaceType 
                   FROM places p 
                   LEFT JOIN municipalities m ON p.Muni_ID = m.Muni_ID 
                   WHERE p.PlaceType = ? AND p.Place_ID != ? 
                   LIMIT 3";

    $stmt_places = $conn->prepare($sql_places);
    $stmt_places->bind_param('si', $place_type, $exclude_place_id);
    $stmt_places->execute();
    $result_places = $stmt_places->get_result();
    $places = $result_places->fetch_all(MYSQLI_ASSOC);

    if ($places) {
        foreach ($places as $place) {
            echo "
            <div class='col-md-4 mb-4 place-card' data-latitude='" . htmlspecialchars($place['Latitude']) . "' data-longitude='" . htmlspecialchars($place['Longitude']) . "' data-name='" . htmlspecialchars($place['PlaceName']) . "' data-type='" . htmlspecialchars($place['PlaceType']) . "'>
                <a href='place-details.php?Place_ID=" . htmlspecialchars($place['Place_ID']) . "' class='card-link'>
                    <div class='card tourism-card border-0 text-white' style='border-radius: 15px; overflow: hidden; width: 100%; height: 350px;'>
                        <img src='" . htmlspecialchars($place['PlacePicture']) . "' class='card-img' style='height: 100%; object-fit: cover; border-radius: 15px;' alt='" . htmlspecialchars($place['PlaceName']) . "'>
                        <div class='card-img-overlay d-flex flex-column justify-content-end p-3' style='background: rgba(0, 0, 0, 0.4);'>
                            <h5 class='card-title mb-0 text-center text-warning'>" . htmlspecialchars($place['PlaceName']) . "</h5>
                            <p class='card-text text-center text-light'><i class='fas fa-map-marker-alt'></i> " . htmlspecialchars($place['PlaceLocation']) . "</p>
                        </div>
                    </div>
                </a>
            </div>
        ";
        }
    } else {
        echo "<div class='col-12'><p class='text-center text-muted'>No similar places found.</p></div>";
    }
} else {
    echo "<div class='col-12'><p class='text-center text-muted'>Invalid request.</p></div>";
}
