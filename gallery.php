<?php
session_start();
// Database connection
include 'connection/connection.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    
}

 
$isLoggedIn = isset($_SESSION['username']); 

// Fetch place types from the database
$sql_place_types = "SELECT PlaceType, Icon FROM place_types";
$result_place_types = $conn->query($sql_place_types);

$place_types = [];
if ($result_place_types && $result_place_types->num_rows > 0) {
    while ($row = $result_place_types->fetch_assoc()) {
        $place_types[] = $row;
    }
}

// Fetch municipality names for the dropdown, ordered alphabetically
$sql_municipalities = "SELECT Muni_ID, MuniName FROM municipalities ORDER BY MuniName ASC";
$result_municipalities = $conn->query($sql_municipalities);

$municipalities = [];
if ($result_municipalities && $result_municipalities->num_rows > 0) {
    while ($row = $result_municipalities->fetch_assoc()) {
        $municipalities[] = $row;
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Gallery</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style-2.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        .filter-card {
            margin-bottom: 20px;
        }

        .filter-item {
            cursor: pointer;
            transition: transform 0.4s, background-color 0.4s;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-right: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .filter-item:hover,
        .filter-item.active {
            transform: scale(1.1);
            background-color: #28a745;
            color: #fff;
        }

        .filter-item img {
            width: 20px;
            height: 20px;
            margin-right: 8px;
        }

        .filter-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .filter-row .form-select {
            margin-right: 10px;
            width: 300px;
        }

        /* Spinner */
        #loading-spinner {
            display: none;
            text-align: center;
        }

        .spinner-border {
            color: #28a745;
        }

        /* Gallery items */
        .gallery-item {
            margin-bottom: 20px;
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .gallery-item.show {
            opacity: 1;
            transform: scale(1);
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.5s, box-shadow 0.5s;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            transition: transform 0.5s, filter 0.5s;
        }

        .card-img-top:hover {
            transform: scale(1.1);
            filter: brightness(80%);
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.5s ease;
            text-align: center;
        }

        .card:hover .overlay {
            opacity: 1;
        }

        .location-link {
            color: #FFD700;
            text-decoration: none;
        }

        .location-link:hover {
            text-decoration: underline;
        }

        .pagination {
            justify-content: center;
        }

        .pagination .page-item .page-link {
            border: none;
        }

        .pagination .page-item.active .page-link {
            background-color: #28a745;
            color: white;
        }

        .pagination .page-item .page-link:hover {
            background-color: #218838;
            color: white;
        }
    </style>
</head>

<body>

    <header class="header_area">
        <div class="main_menu">
            <?php include 'includes/navbar.php'; ?>
        </div>
    </header>


    <main class="container mt-4">
        <!-- Filter Card -->
        <div class="filter-card card p-4 mb-4">
            <div class="card-body">
                <h5 class="card-title">Filter Options</h5>

                <!-- Place Type Filter Row -->
                <div class="place-type-filter mb-3 d-flex">
                    <div class="filter-item active" data-category="all">All</div>
                    <?php foreach ($place_types as $type): ?>
                        <div class="filter-item rounded-pill shadow-sm mx-2 p-2 text-center" data-category="<?php echo htmlspecialchars($type['PlaceType']); ?>">
                            <img src="<?php echo 'assets/images/icon/' . htmlspecialchars($type['Icon']); ?>" alt="<?php echo htmlspecialchars($type['PlaceType']); ?>">
                            <?php echo htmlspecialchars($type['PlaceType']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Municipality Dropdown and Search Input (Same Row) -->
                <div class="filter-row">
                    <!-- Municipality Dropdown -->
                    <select id="municipality" class="form-select">
                        <option value="all">Select Municipality</option>
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?php echo htmlspecialchars($municipality['Muni_ID']); ?>">
                                <?php echo htmlspecialchars($municipality['MuniName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Search Input -->
                    <input type="text" id="search" class="form-control" placeholder="Search by title..." style="flex-grow: 1;">
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Gallery Row -->
        <div class="row" id="gallery"></div>

        <nav aria-label="Page navigation">
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </main>

    <?php include 'includes/footer.php' ?>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        let currentPage = 1;
        const itemsPerPage = 20;
        const searchInput = document.getElementById('search');
        const municipalitySelect = document.getElementById('municipality');
        const placeTypeFilters = document.querySelectorAll('.filter-item');
        let currentPlaceType = 'all';
        let currentMunicipality = 'all';

        // Add click event listeners for place type filters
        placeTypeFilters.forEach(filter => {
            filter.addEventListener('click', () => {
                // Set active class
                placeTypeFilters.forEach(f => f.classList.remove('active'));
                filter.classList.add('active');

                currentPlaceType = filter.getAttribute('data-category');
                fetchPlaces();
            });
        });

        // Add change event listener for the municipality dropdown
        municipalitySelect.addEventListener('change', () => {
            currentMunicipality = municipalitySelect.value;
            fetchPlaces();
        });

        // Add instant search functionality
        searchInput.addEventListener('input', () => {
            fetchPlaces();
        });

        // Fetch places with AJAX
        function fetchPlaces() {
            // Show loading spinner
            document.getElementById('loading-spinner').style.display = 'block';

            $.ajax({
                url: 'ajax_content/load_gallery.php',
                method: 'GET',
                data: {
                    place_type: currentPlaceType,
                    municipality: currentMunicipality,
                    search: searchInput.value
                },
                dataType: 'json',
                success: function(data) {
                    displayGallery(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                },
                complete: function() {
                    // Hide loading spinner
                    document.getElementById('loading-spinner').style.display = 'none';
                }
            });
        }

        // Display gallery cards with fade-in effect
        function displayGallery(items) {
            const gallery = document.getElementById('gallery');
            gallery.innerHTML = ''; // Clear existing gallery

            items.forEach(item => {
                const yearCreated = new Date(item.created_at).getFullYear();
                const galleryCard = `
                    <div class="col-md-4 gallery-item show">
                        <div class="card">
                            <img src="assets/images/gallery/${item.image_path}" class="card-img-top" alt="${item.title}">
                            <div class="overlay">
                                <h5>${item.title}</h5>
                                <p>${item.MuniName} - ${yearCreated}</p>
                                <a href="https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}" target="_blank" class="location-link">Get Directions</a>
                            </div>
                        </div>
                    </div>`;
                gallery.innerHTML += galleryCard;

                // Add a delay to show the items with animation
                setTimeout(() => {
                    document.querySelectorAll('.gallery-item').forEach((item, index) => {
                        item.style.transitionDelay = `${index * 0.1}s`;
                        item.classList.add('show');
                    });
                }, 100);
            });
        }

        // Initial load
        fetchPlaces();
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
</body>

</html>