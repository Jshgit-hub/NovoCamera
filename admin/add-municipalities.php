<?php
session_start();
include '../connection/connection.php';

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch/dist/geosearch.css" />
    <title>Add Municipality</title>
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }

        .suggestions-box {
            border: 1px solid #ccc;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            position: absolute;
            width: 100%;
            z-index: 1000;
        }

        .suggestions-box div {
            padding: 8px;
            cursor: pointer;
        }

        .suggestions-box div:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include("includes/adminsidebar.php"); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <?php include('includes/navbar-top.php'); ?>
            </nav>
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 text-light">Add Municipality</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <form action="../Backend/add-muni-backend.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="municipalityName" class="form-label">Municipality Name</label>
                                    <input type="text" class="form-control" name="MuniName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="municipalityDescription" class="form-label">Municipality Description</label>
                                    <textarea class="form-control" id="MuniDesc" name="MuniDesc" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="imageUpload" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" id="imageUpload" name="MuniPicture" accept="image/*" required>
                                </div>
                                <div class="mb-3">
                                    <label for="population" class="form-label">Population</label>
                                    <input type="text" class="form-control" id="population" name="Population" placeholder="Enter population (e.g., 10,000)" required>
                                </div>
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area (in square kilometers)</label>
                                    <input type="text" class="form-control" id="area" name="Area" placeholder="Enter area (e.g., 123.45)" required>
                                </div>
                                <div class="mb-3">
                                    <label for="established" class="form-label">Established Date</label>
                                    <input type="date" class="form-control" name="Established">
                                </div>
                                <div class="mb-3">
                                    <label for="mayor" class="form-label">Mayor</label>
                                    <input type="text" class="form-control" name="Mayor" required>
                                </div>
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location_search" name="Location" required>
                                    <div id="suggestions" class="suggestions-box"></div>
                                </div>

                                <!-- Map for selecting location -->
                                <div id="map"></div>

                                <!-- Latitude and Longitude inputs -->
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">Add Municipality</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-geosearch/dist/geosearch.umd.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#MuniDesc',
            plugins: 'lists link image preview', // Remove 'print' from the list
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | link image',
            branding: false,
            content_style: "body { font-family: 'Poppins', sans-serif; font-size: 14px; color: #333; }"
        });

        // Initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([15.5, 120.5], 10); // Example center coordinates

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a draggable marker to the map
            var marker = L.marker([15.5, 120.5], {
                draggable: true
            }).addTo(map);

            // Event listener to update the form inputs with the marker's current location
            marker.on('dragend', function(e) {
                var lat = marker.getLatLng().lat;
                var lng = marker.getLatLng().lng;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });

            // Set up the geosearch provider
            const provider = new window.GeoSearch.OpenStreetMapProvider();

            // Listen for input in the search box
            let searchTimeout;
            document.getElementById('location_search').addEventListener('input', function(e) {
                const query = e.target.value;
                const suggestionsBox = document.getElementById('suggestions');

                // Delay the search to avoid too many API calls
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(async function() {
                    if (query.length > 2) { // Start searching after the user has typed 3 characters
                        const results = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                            .then(response => response.json());

                        suggestionsBox.innerHTML = ''; // Clear previous suggestions

                        if (results && results.length > 0) {
                            results.forEach(result => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.textContent = result.display_name;
                                suggestionItem.addEventListener('click', function() {
                                    // Update marker position and map view
                                    const lat = result.lat;
                                    const lng = result.lon;
                                    marker.setLatLng([lat, lng]);
                                    map.setView([lat, lng], 15);

                                    // Update form inputs
                                    document.getElementById('latitude').value = lat;
                                    document.getElementById('longitude').value = lng;

                                    // Set the location search box value
                                    document.getElementById('location_search').value = result.display_name;

                                    // Clear suggestions
                                    suggestionsBox.innerHTML = '';
                                });
                                suggestionsBox.appendChild(suggestionItem);
                            });
                        }
                    } else {
                        suggestionsBox.innerHTML = ''; // Clear suggestions if query is too short
                    }
                }, 300); // 300ms delay for search
            });

            // Hide suggestions box when clicking outside
            document.addEventListener('click', function(e) {
                if (!document.getElementById('location_search').contains(e.target)) {
                    document.getElementById('suggestions').innerHTML = '';
                }
            });
        });

        // Format population and area inputs
        document.getElementById('population').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });

        document.getElementById('area').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });
    </script>
</body>

</html>