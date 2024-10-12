<?php
include('../connection/connection.php');

$muni_id = $_GET['Muni_ID'];
$exclude_place_id = $_GET['exclude_place_id'];

$sql = "SELECT * FROM places WHERE Muni_ID = ? AND Place_ID != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $muni_id, $exclude_place_id);
$stmt->execute();
$result = $stmt->get_result();

$places = [];
while ($row = $result->fetch_assoc()) {
    $places[] = $row;
}

$stmt->close();
$conn->close();

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
?>
