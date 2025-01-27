<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$isLoggedIn = isset($_SESSION['username']); // Adjusted to 'username' based on your session variable in the navbar


// Fetch PlaceTypes including Icons
$sql_place_types = "SELECT * FROM place_types";
$result_place_types = mysqli_query($conn, $sql_place_types);

$place_types = [];
if ($result_place_types) {
    while ($row = mysqli_fetch_assoc($result_place_types)) {
        $place_types[] = $row;
    }
}

mysqli_close($conn);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Places in Nueva Ecija</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="css/style-2.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        .filter-item {
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.4s;
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .filter-item:hover {
            transform: scale(1.1);
            background-color: #28a745;
            color: #fff;
        }

        .filter-item img {
            width: 20px;
            height: 20px;
            margin-right: 8px;
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.5s;
        }

        .card:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), filter 0.5s;
        }

        .card-img-top:hover {
            transform: scale(1.15);
            filter: brightness(80%);
        }

        .card-img-overlay {
            opacity: 0;
            transition: opacity 0.6s ease, transform 0.6s ease;
            transform: translateY(20px);
        }

        .card:hover .card-img-overlay {
            opacity: 1;
            transform: translateY(0);
        }

        .animate-fade-in {
            animation: fadeInUp 0.8s ease-in-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .video-section {
            position: relative;
            height: 70vh;
            background: #000;
            overflow: hidden;
        }

        .video-section video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.5;
        }

        .video-section .content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-align: center;
            z-index: 2;
        }

        .video-section h2 {
            opacity: 0;
            animation: fadeInLeft 1.5s ease-in-out forwards;
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        #map {
            height: 400px;
            position: relative;
            z-index: 1;
        }

        .header_area {
            z-index: 1000;
            position: relative;
        }

        /* Cinematic hover effect for filter items */
        .filter-item.active,
        .filter-item:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <header class="header_area">
        <div class="main_menu">
            <?php include 'includes/navbar.php'; ?>
        </div>
    </header>

    <section class="video-section">
        <video autoplay muted loop>
            <source src="assets/video/Gabaldon Nueva Ecija _ 4k Aerial Footage.mp4" type="video/mp4">
            Your browser does not support HTML5 video.
        </video>
        <div class="content">
            <h2 class="animate__animated animate__fadeInLeft text-light display-5 fw-bold">
                Immerse Yourself in <span style="color: #28a745;">Nature</span>
            </h2>
        </div>
    </section>


    <div class="container mt-4">

        <!-- Map Section -->
        <div id="map" class="mb-4 rounded" style="height: 400px;"></div>


        <div class="container mt-4">
            <div class="row">
                <!-- Filter Section and Search Bar -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <!-- Filter Section -->
                    <div class="filter-slider d-flex flex-grow-1 overflow-auto me-3">
                        <div class="filter-item rounded-pill shadow-sm mx-2 p-2 text-center" data-category="all">
                            All
                        </div>
                        <?php foreach ($place_types as $type): ?>
                            <div class="filter-item rounded-pill shadow-sm mx-2 p-2 text-center" data-category="<?php echo htmlspecialchars($type['PlaceType']); ?>">
                                <img src="<?php echo 'assets/images/icon/' . htmlspecialchars($type['Icon']); ?>" alt="<?php echo htmlspecialchars($type['PlaceType']); ?>">
                                <?php echo htmlspecialchars($type['PlaceType']); ?>
                            </div>

                        <?php endforeach; ?>
                    </div>

                    <!-- Search Bar -->
                    <div class="input-group" style="flex-shrink: 0; width: 200px;">
                        <input type="text" id="search-input" class="form-control" placeholder="Search">
                        <button class="btn btn-success" id="search-button">Search</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Places Section -->
        <h4 class="mb-4">Explore the Beauty of Nueva Ecija</h4>
        <div class="row">
            <!-- Cards Section -->
            <div class="col-md-12">
                <div class="row" id="places-container">
                    <!-- Places will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php' ?>

    <!-- JavaScript for Map and Filtering -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="js/main.js"></script>

    <script>
        $(document).ready(function() {
            let map;
            let markers = [];

            // Initialize the map
            function initMap() {
                map = L.map('map').setView([15.5812, 120.8486], 10);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
            }

            // Clear all markers from the map
            function clearMarkers() {
                markers.forEach(marker => map.removeLayer(marker));
                markers = [];
            }

            // Function to fetch places based on the selected category or search query
            function fetchPlaces(place_type = 'all', search_query = '') {
                $.ajax({
                    url: 'ajax_content/fetch-places-all.php',
                    type: 'POST',
                    data: {
                        place_type: place_type,
                        search_query: search_query
                    },
                    success: function(response) {
                        $('#places-container').html(response); // Update the places container with the fetched data
                        clearMarkers(); // Clear existing markers from the map

                        // Extract place data from the HTML elements to add markers to the map
                        $('#places-container .place-card').each(function() {
                            const latitude = $(this).data('latitude');
                            const longitude = $(this).data('longitude');
                            const placeName = $(this).data('name');
                            const placeType = $(this).data('type');
                            const placeImage = $(this).data('image'); // Fetch PlacePicture from data-image attribute
                            const placeLink = $(this).data('link'); // Fetch Place link from data-link attribute

                            const customIcon = L.icon({
                                iconUrl: `assets/images/icon/${placeType.toLowerCase()}.png`,
                                iconSize: [32, 32],
                                className: 'custom-icon'
                            });

                            // Add marker to map with image and link in the popup
                            const marker = L.marker([latitude, longitude], {
                                icon: customIcon
                            }).bindPopup(`
                  <div class="custom-popup" style="position: relative; width: 150px; height: 150px; overflow: hidden; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: transform 0.3s;">
    <img src="${placeImage}" alt="${placeName}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
    <div class="overlay-content" style="position: absolute; bottom: 0; left: 0; width: 100%; background: rgba(0, 0, 0, 0.5); color: white; padding: 10px; text-align: center;">
        <p style="margin: 0;">${placeName}</p>
    </div>
    <a href="${placeLink}" target="_blank" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; text-decoration: none;"></a>
</div>


                `).addTo(map);

                            markers.push(marker);
                        });

                        // Add fade-in animation to the cards
                        $('.place-card').addClass('animate-fade-in');
                    },
                    error: function() {
                        alert('An error occurred while fetching the places.');
                    }
                });
            }


            // Initialize the map
            initMap();

            // Load all places on page load
            fetchPlaces('all');

            // Event listener for filter buttons
            $('.filter-item').on('click', function() {
                const placeType = $(this).data('category');
                fetchPlaces(placeType, $('#search-input').val());

                // Highlight active filter
                $('.filter-item').removeClass('active');
                $(this).addClass('active');
            });

            // Event listener for search input to update places dynamically while typing
            $('#search-input').on('input', function() {
                const searchQuery = $(this).val();
                const placeType = $('.filter-item.active').data('category') || 'all';
                fetchPlaces(placeType, searchQuery);
            });

            // Optional: Make the first filter button active by default
            $('.filter-item[data-category="all"]').addClass('active');

            // IntersectionObserver to trigger fade-in animations for content
            const fadeIns = document.querySelectorAll('.animate-fade-in');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                    }
                });
            }, {
                threshold: 0.1
            });

            fadeIns.forEach(fadeIn => {
                observer.observe(fadeIn);
            });
        });
    </script>

</body>

</html>