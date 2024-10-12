<?php
include("../connection/connection.php");

if (isset($_POST['place_type'])) {
    $place_type = htmlspecialchars($_POST['place_type']);
    $search_query = isset($_POST['search_query']) ? '%' . htmlspecialchars($_POST['search_query']) . '%' : '%';

    $sql_places = "SELECT p.Place_ID, p.PlaceName, p.PlacePicture, p.Latitude, p.Longitude, p.PlaceLocation, m.MuniName, p.PlaceType 
                   FROM places p 
                   LEFT JOIN municipalities m ON p.Muni_ID = m.Muni_ID 
                   WHERE p.PlaceName LIKE ?";

    if ($place_type !== 'all') {
        $sql_places .= " AND p.PlaceType = ?";
    }

    $stmt_places = $conn->prepare($sql_places);

    if ($place_type !== 'all') {
        $stmt_places->bind_param('ss', $search_query, $place_type);
    } else {
        $stmt_places->bind_param('s', $search_query);
    }

    $stmt_places->execute();
    $result_places = $stmt_places->get_result();
    $places = $result_places->fetch_all(MYSQLI_ASSOC);

    if ($places) {
        foreach ($places as $place) {
            // Assuming PlacePicture already holds the correct path for map images (no additional directory path prepended)
            echo "
            <div class='col-md-4 mb-4 place-card' 
                 data-latitude='" . htmlspecialchars($place['Latitude']) . "' 
                 data-longitude='" . htmlspecialchars($place['Longitude']) . "' 
                 data-name='" . htmlspecialchars($place['PlaceName']) . "' 
                 data-type='" . htmlspecialchars($place['PlaceType']) . "' 
                 data-image='" . htmlspecialchars($place['PlacePicture']) ."'
                 data-link='place-details.php?Place_ID=" . htmlspecialchars($place['Place_ID']) . "'>
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
        echo "<div class='col-12'><p class='text-center text-light'>No places found.</p></div>";
    }
} else {
    echo "<div class='col-12'><p class='text-center text-light'>Invalid request.</p></div>";
}
