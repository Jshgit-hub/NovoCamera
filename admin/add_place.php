<?php
session_start();
include '../connection/connection.php';

// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

$municipalities = [];

// If the user is a superadmin, fetch all municipalities
if ($_SESSION['role'] === 'superadmin') {
    $sql = "SELECT Muni_ID, MuniName FROM municipalities";
} else {
    // If the user is an admin, fetch only the municipality assigned to them
    $admin_muni_id = $_SESSION['Muni_ID']; // Assume this is set during login
    $sql = "SELECT Muni_ID, MuniName FROM municipalities WHERE Muni_ID = $admin_muni_id";
}

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }
}

// Fetch PlaceTypes from the database
$placeTypes = [];
$sql_place_types = "SELECT id, PlaceType FROM place_types";
$result_place_types = $conn->query($sql_place_types);
if ($result_place_types && $result_place_types->num_rows > 0) {
    while ($row = $result_place_types->fetch_assoc()) {
        $placeTypes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Place</title>
    <link href="css/app.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch/dist/geosearch.css" />
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
                            <h5 class="card-title mb-0 text-light">Add Place</h5>
                        </div>
                        <div class="card-body">
                            <form action="controller/add_place_backend.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="PlaceName" class="form-label">Place Name</label>
                                    <input type="text" class="form-control" id="PlaceName" name="PlaceName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="PlaceType" class="form-label">Place Type</label>
                                    <select class="form-control" id="PlaceType" name="PlaceType" required>
                                        <?php foreach ($placeTypes as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['PlaceType']); ?>">
                                                <?php echo htmlspecialchars($type['PlaceType']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="Muni_ID" class="form-label">Municipality</label>
                                    <select class="form-control" id="Muni_ID" name="Muni_ID" required>
                                        <?php foreach ($municipalities as $municipality): ?>
                                            <option value="<?php echo $municipality['Muni_ID']; ?>">
                                                <?php echo $municipality['MuniName']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="PlaceDescription" class="form-label">Place Description</label>
                                    <textarea class="form-control" id="PlaceDescription" name="PlaceDescription" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="PlaceLocation" class="form-label">Search Place Location</label>
                                    <input type="text" class="form-control" id="location_search" name="PlaceLocation" required>
                                    <div id="suggestions" class="suggestions-box"></div>
                                </div>
                                <div id="map"></div>
                                <!-- Latitude and Longitude -->
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="PlaceHeaderImage" class="form-label">Upload Header Image</label>
                                    <input type="file" class="form-control" id="PlaceHeaderImage" name="PlaceHeaderImage" accept="image/*" required>
                                </div>
                                <div class="mb-3">
                                    <label for="PlacePictures" class="form-label">Upload Additional Images for Carousel</label>
                                    <input type="file" class="form-control" id="PlacePictures" name="PlacePictures[]" accept="image/*" multiple required>
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">Add Place</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://unpkg.com/leaflet-geosearch/dist/geosearch.umd.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#PlaceDescription',
            plugins: 'lists link image preview', 
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | link image',
            branding: false,
            content_style: "body { font-family: 'Poppins', sans-serif; font-size: 14px; color: #333; }"
        });

        // Initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([15.5, 120.5], 10);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a draggable marker to the map
            var marker = L.marker([15.5, 120.5], {
                draggable: true
            }).addTo(map);

            // Update form inputs with the marker's current location
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
                    if (query.length > 2) { 
                        const results = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                            .then(response => response.json());

                        suggestionsBox.innerHTML = ''; 

                        if (results && results.length > 0) {
                            results.forEach(result => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.textContent = result.display_name;
                                suggestionItem.addEventListener('click', function() {
                                    const lat = result.lat;
                                    const lng = result.lon;
                                    marker.setLatLng([lat, lng]);
                                    map.setView([lat, lng], 15);

                                    document.getElementById('latitude').value = lat;
                                    document.getElementById('longitude').value = lng;

                                    document.getElementById('location_search').value = result.display_name;

                                    suggestionsBox.innerHTML = '';
                                });
                                suggestionsBox.appendChild(suggestionItem);
                            });
                        }
                    } else {
                        suggestionsBox.innerHTML = ''; 
                    }
                }, 300); 
            });

            // Hide suggestions box when clicking outside
            document.addEventListener('click', function(e) {
                if (!document.getElementById('location_search').contains(e.target)) {
                    document.getElementById('suggestions').innerHTML = '';
                }
            });
        });
    </script>
</body>

</html>
