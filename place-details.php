<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['Place_ID'])) {
    $place_id = intval($_GET['Place_ID']);

    // Fetch place details along with MuniName
    $sql_place = "
        SELECT p.*, m.MuniName 
        FROM places p
        LEFT JOIN municipalities m ON p.Muni_ID = m.Muni_ID
        WHERE p.Place_ID = ?";
    $stmt_place = $conn->prepare($sql_place);
    $stmt_place->bind_param('i', $place_id);
    $stmt_place->execute();
    $result_place = $stmt_place->get_result();
    $place = $result_place->fetch_assoc();

    if (!$place) {
        echo "Place not found!";
        exit;
    }

    // Fetch carousel images
    $sql_images = "SELECT * FROM place_images WHERE Place_ID = ?";
    $stmt_images = $conn->prepare($sql_images);
    $stmt_images->bind_param('i', $place_id);
    $stmt_images->execute();
    $images = $stmt_images->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt_place->close();
    $stmt_images->close();
} else {
    echo "No place ID provided!";
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/Fevicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style-2.css">
    <title><?php echo htmlspecialchars($place['PlaceName']); ?> - Place Details</title>
    <style>
        /* Header Image Styling */
        .header-image {
            background: url('<?php echo htmlspecialchars($place['PlacePicture']); ?>') no-repeat center center/cover;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
        }

        #map {
            height: 400px;
            position: relative;
            /* Ensure position is set */
            z-index: 1;
            /* Set z-index higher than other elements */
        }

        .header_area {
            z-index: 1000;
            /* Keep the navbar on top */
            position: relative;
            /* Ensure z-index works */
        }

        .header-image h2 {
            font-size: 48px;
            font-weight: bold;
            text-transform: uppercase;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
        }

        /* Main Content Styling */
        .main_place_details {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .main_place_details img {
            max-height: 450px;
            object-fit: cover;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .main_place_details h4 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        .main_place_details p {
            line-height: 1.8;
            font-size: 16px;
            color: #666;
        }

        /* Map Styling */
        #map {
            height: 400px;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        /* Card Styling */
        .place-card .card-img-overlay {
            opacity: 0;
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
            transform: translateY(20px);
        }

        .place-card .card {
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease-in-out;
        }

        .place-card .card:hover {
            transform: scale(1.05);
        }

        .place-card .card:hover .card-img-overlay {
            opacity: 1;
            transform: translateY(0);
        }

        .place-card .card-img {
            transition: transform 0.3s ease-in-out;
        }

        .place-card .card:hover .card-img {
            transform: scale(1.1);
        }

        .place-card .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .place-card .card-text {
            font-size: 0.875rem;
        }
    </style>
</head>

<body>

    <header class="header_area">
        <div class="main_menu">
            <?php include 'includes/navbar.php' ?>
        </div>
    </header>


    <!-- Header Image Section -->
    <section class="header-image">
        <h2 class="text-light"><?php echo ($place['PlaceName']); ?></h2>
    </section>



    <section class="back-button-section py-3">
        <div class="container">
            <nav aria-label="breadcrumb" class="container mt-4">
                <ol class="breadcrumb" style="background-color: #f1f1f1; padding: 10px; border-radius: 8px;">
                    <li class="breadcrumb-item">
                        <a href="places.php" style="color: #379777; font-size: 1.25rem;">Places</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #45474B; font-size: 1.25rem;">
                        <?php echo htmlspecialchars($place['PlaceName']); ?>
                    </li>
                </ol>
            </nav>


        </div>
    </section>

    <!-- Main Place Details Section -->
    <section class="place-details section-margin">
        <div class="container">
            <div class="main_place_details">
                <p class="text-muted"><?php echo htmlspecialchars($place['PlaceLocation']); ?></p>
                <div class="mb-4">
                    <?php if (count($images) > 1): ?>
                        <div id="placeCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="../assets/images/ad-places/carousel/<?php echo htmlspecialchars($image['ImagePath']); ?>" class="d-block w-100" alt="...">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#placeCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#placeCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <img class="img-fluid" src="../assets/images/ad-places/carousel/<?php echo htmlspecialchars($images[0]['ImagePath']); ?>" alt="<?php echo htmlspecialchars($place['PlaceName']); ?>">
                    <?php endif; ?>
                </div>
                <p><?php echo nl2br(($place['PlaceDescription'])); ?></p>

                <!-- Map Section -->
                <div class="h1 pt-5 pb-3 text-success">Get Directions</div>
                <?php if (!empty($place['Latitude']) && !empty($place['Longitude'])): ?>
                    <div id="map"></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Explore More Section -->
    <section id="tourism-places" class="py-5 bg-dark">
        <div class="container">
            <div class="text-center mb-5">
                <h3 class="fs-6 text-uppercase text-success">Tourism</h3>
                <h2 class="fs-1 text-light">Places to Visit in <?php echo htmlspecialchars($place['MuniName']); ?></h2>
            </div>
            <div class="row" id="places-container">
                <!-- Places will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <!-- More Like This Section -->
    <section id="Municipal-places" class="py-5 bg-light">
        <div class="container">
            <div class="text-start mb-5">
                <h3 class="fs-6 text-uppercase text-warning">More like this</h3>
                <h2 class="fs-1">Explore destinations within <?php echo htmlspecialchars($place['MuniName']); ?></h2>
            </div>
            <div class="row" id="Municipal-container">
                <!-- Municipalities will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            const muniId = <?php echo $place['Muni_ID']; ?>;
            const placeType = '<?php echo $place['PlaceType']; ?>';
            const currentPlaceId = <?php echo $place['Place_ID']; ?>;

            fetchPlaces(muniId, currentPlaceId);
            fetchSimilarPlaces(placeType, currentPlaceId);

            function fetchPlaces(muniId, currentPlaceId) {
                $.ajax({
                    url: '../ajax_content/fetch_on-places.php',
                    type: 'GET',
                    data: {
                        Muni_ID: muniId,
                        exclude_place_id: currentPlaceId
                    },
                    success: function(response) {
                        $('#places-container').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ', status, error);
                        $('#places-container').html('<div class="text-center text-muted">Failed to load places.</div>');
                    }
                });
            }

            function fetchSimilarPlaces(placeType, currentPlaceId) {
                $.ajax({
                    url: 'ajax_content/fetch_similar_places.php',
                    type: 'GET',
                    data: {
                        PlaceType: placeType,
                        exclude_place_id: currentPlaceId
                    },
                    success: function(response) {
                        // Directly insert the response HTML into the container
                        $('#Municipal-container').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ', status, error);
                        $('#Municipal-container').html('<div class="text-center text-muted">Failed to load similar places.</div>');
                    }
                });
            }

        });
    </script>

    <script>
        $(document).ready(function() {
            <?php if (!empty($place['Latitude']) && !empty($place['Longitude'])): ?>
                var map = L.map('map').setView([<?php echo $place['Latitude']; ?>, <?php echo $place['Longitude']; ?>], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                var marker = L.marker([<?php echo $place['Latitude']; ?>, <?php echo $place['Longitude']; ?>]).addTo(map);

                marker.bindPopup("<b><?php echo htmlspecialchars($place['PlaceName']); ?></b><br><a href='https://www.google.com/maps/dir/?api=1&destination=<?php echo $place['Latitude']; ?>,<?php echo $place['Longitude']; ?>' target='_blank'>Get Directions</a>").openPopup();
            <?php endif; ?>
        });
    </script>

</body>

</html>