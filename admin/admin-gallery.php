<?php
session_start();
include '../connection/connection.php';

// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

// Fetch the admin's Muni_ID if they are logged in as admin
$admin_muni_id = null;
if ($_SESSION['role'] === 'admin') {
    $user_id = $_SESSION['user_id']; // Assuming the user_id is stored in session
    $sql_muni_id = "SELECT Muni_ID FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_muni_id);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $admin_muni_id = $row['Muni_ID'];
    }
    $stmt->close();
}

// Fetch municipalities
$municipalities = [];
if ($_SESSION['role'] === 'superadmin') {
    $sql_municipalities = "SELECT Muni_ID, MuniName FROM municipalities";
} else {
    $sql_municipalities = "SELECT Muni_ID, MuniName FROM municipalities WHERE Muni_ID = ?";
}
$stmt = $conn->prepare($sql_municipalities);
if ($_SESSION['role'] === 'admin') {
    $stmt->bind_param("i", $admin_muni_id);
}
$stmt->execute();
$result_municipalities = $stmt->get_result();
while ($row = $result_municipalities->fetch_assoc()) {
    $municipalities[] = $row;
}
$stmt->close();

// Fetch place types
$place_types = [];
$sql_place_types = "SELECT id, PlaceType FROM place_types";
$result_place_types = $conn->query($sql_place_types);
while ($row = $result_place_types->fetch_assoc()) {
    $place_types[] = $row;
}

// Set pagination values for places
$items_per_page_places = 10;
$page_places = isset($_GET['page_places']) ? (int)$_GET['page_places'] : 1;
$offset_places = ($page_places - 1) * $items_per_page_places;

// Fetch images from the places table with pagination
$places_images = [];
$sql_places_images = "SELECT Place_ID, PlacePicture AS ImagePath, PlaceName FROM places WHERE PlacePicture IS NOT NULL LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_places_images);
$stmt->bind_param("ii", $items_per_page_places, $offset_places);
$stmt->execute();
$result_places_images = $stmt->get_result();
if ($result_places_images && $result_places_images->num_rows > 0) {
    while ($row = $result_places_images->fetch_assoc()) {
        $row['ImagePath'] = "../assets/images/ad-places/" . basename($row['ImagePath']);
        $places_images[] = $row;
    }
}
$stmt->close();

// Fetch total number of places images for pagination
$sql_count_places_images = "SELECT COUNT(*) AS total_images FROM places WHERE PlacePicture IS NOT NULL";
$result_count_places = $conn->query($sql_count_places_images);
$total_places_images = $result_count_places->fetch_assoc()['total_images'];
$total_pages_places = ceil($total_places_images / $items_per_page_places);

// Set pagination values for posts
$items_per_page_posts = 10;
$page_posts = isset($_GET['page_posts']) ? (int)$_GET['page_posts'] : 1;
$offset_posts = ($page_posts - 1) * $items_per_page_posts;
$post_images = [];
$sql_post_images = "SELECT post.post_id AS Image_ID, post.image_url AS ImagePath, post.title AS PlaceName, users.username FROM post JOIN users ON post.user_id = users.user_id WHERE post.image_url IS NOT NULL LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_post_images);
$stmt->bind_param("ii", $items_per_page_posts, $offset_posts);
$stmt->execute();
$result_post_images = $stmt->get_result();
if ($result_post_images && $result_post_images->num_rows > 0) {
    while ($row = $result_post_images->fetch_assoc()) {
        $image_url = $row['ImagePath'];
        if (strpos($image_url, '../assets/images/userUpload/') !== false) {
            $row['ImagePath'] = $image_url;
        } else {
            $row['ImagePath'] = "../uploads/" . basename($image_url);
        }
        $post_images[] = $row;
    }
}
$stmt->close();

// Fetch total number of post images for pagination
$sql_count_post_images = "SELECT COUNT(*) AS total_images FROM post WHERE image_url IS NOT NULL";
$result_count_posts = $conn->query($sql_count_post_images);
$total_post_images = $result_count_posts->fetch_assoc()['total_images'];
$total_pages_posts = ceil($total_post_images / $items_per_page_posts);

// Determine which images to display based on the filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'places';
$images = ($filter === 'posts') ? $post_images : $places_images;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <link href="css/app.css" rel="stylesheet">
    <style>
        .card-img-top {
            max-height: 150px;
            object-fit: cover;
        }

        .filter-buttons {
            margin-bottom: 20px;
        }

        .image-container {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 15px;
        }

        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #000;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
        }

        .pagination a:hover {
            background-color: #ddd;
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

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 text-light">Manage Gallery</h5>
                        </div>
                        <div class="card-body">
                            <div class="filter-buttons">
                                <a href="?filter=places" class="btn btn-secondary <?php echo ($filter === 'places') ? 'active' : ''; ?>">Places Images</a>
                                <a href="?filter=posts" class="btn btn-secondary <?php echo ($filter === 'posts') ? 'active' : ''; ?>">Posts Images</a>
                            </div>

                            <form action="controller/add_gallery_image.php" method="POST" enctype="multipart/form-data">
                                <h5>Select Existing Pictures</h5>
                                <div class="image-container">
                                    <div class="row">
                                        <?php foreach ($images as $image): ?>
                                            <div class="col-md-3">
                                                <div class="card mb-3">
                                                    <img src="<?php echo htmlspecialchars($image['ImagePath']); ?>" class="card-img-top" alt="...">
                                                    <div class="card-body">
                                                        <p class="card-text">Title: <?php echo htmlspecialchars($image['PlaceName'] ?? ''); ?></p>

                                                        <?php if ($filter === 'places'): ?>
                                                            <p class="card-text">Place ID: <?php echo htmlspecialchars($image['Place_ID']); ?></p>
                                                            <input type="radio" class="form-check-input" name="selected_image" value="place-<?php echo $image['Place_ID']; ?>" required>
                                                        <?php elseif ($filter === 'posts'): ?>
                                                            <p class="card-text">Uploader: <?php echo htmlspecialchars($image['username']); ?></p>
                                                            <input type="radio" class="form-check-input" name="selected_image" value="post-<?php echo $image['Image_ID']; ?>-user-<?php echo $image['username']; ?>" required>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Pagination for Places or Posts -->
                                <?php if ($filter === 'places'): ?>
                                    <div class="pagination">
                                        <?php if ($page_places > 1): ?>
                                            <a href="?filter=places&page_places=<?php echo $page_places - 1; ?>">&laquo; Previous</a>
                                        <?php endif; ?>
                                        <?php for ($i = 1; $i <= $total_pages_places; $i++): ?>
                                            <a href="?filter=places&page_places=<?php echo $i; ?>" class="<?php echo ($i == $page_places) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                        <?php endfor; ?>
                                        <?php if ($page_places < $total_pages_places): ?>
                                            <a href="?filter=places&page_places=<?php echo $page_places + 1; ?>">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($filter === 'posts'): ?>
                                    <div class="pagination">
                                        <?php if ($page_posts > 1): ?>
                                            <a href="?filter=posts&page_posts=<?php echo $page_posts - 1; ?>">&laquo; Previous</a>
                                        <?php endif; ?>
                                        <?php for ($i = 1; $i <= $total_pages_posts; $i++): ?>
                                            <a href="?filter=posts&page_posts=<?php echo $i; ?>" class="<?php echo ($i == $page_posts) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                        <?php endfor; ?>
                                        <?php if ($page_posts < $total_pages_posts): ?>
                                            <a href="?filter=posts&page_posts=<?php echo $page_posts + 1; ?>">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <h5>Add New Image</h5>
                                <div class="mb-3">
                                    <label for="ImageTitle" class="form-label">Image Title</label>
                                    <input type="text" class="form-control" id="ImageTitle" name="ImageTitle" required>
                                </div>

                                <div class="mb-3">
                                    <label for="municipality" class="form-label">Municipality</label>
                                    <select class="form-select" id="municipality" name="municipality" required>
                                        <?php foreach ($municipalities as $municipality): ?>
                                            <option value="<?php echo $municipality['Muni_ID']; ?>"><?php echo $municipality['MuniName']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="place_type" class="form-label">Place Type</label>
                                    <select class="form-select" id="place_type" name="place_type" required>
                                        <?php foreach ($place_types as $type): ?>
                                            <option value="<?php echo $type['id']; ?>"><?php echo $type['PlaceType']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="ImageLocation" class="form-label">Search Image Location</label>
                                    <input type="text" class="form-control" id="location_search" name="ImageLocation" required>
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
                                    <label for="GalleryImage" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" id="GalleryImage" name="GalleryImage" accept="image/*">
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">Add New Image</button>
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
        // Initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([15.5, 120.5], 10);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a marker that the user can drag to select the location
            var marker = L.marker([15.5, 120.5], {
                draggable: true
            }).addTo(map);

            // Update latitude and longitude inputs when the marker is dragged
            marker.on('dragend', function(e) {
                var lat = marker.getLatLng().lat;
                var lng = marker.getLatLng().lng;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });

            // Set up the geosearch provider for location suggestions
            const provider = new window.GeoSearch.OpenStreetMapProvider();

            // Handle search input and display suggestions
            document.getElementById('location_search').addEventListener('input', function(e) {
                const query = e.target.value;
                const suggestionsBox = document.getElementById('suggestions');

                // Fetch suggestions for the location search
                provider.search({
                    query: query
                }).then(function(results) {
                    suggestionsBox.innerHTML = ''; // Clear previous suggestions

                    results.forEach(result => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.textContent = result.label;
                        suggestionItem.addEventListener('click', function() {
                            const lat = result.y;
                            const lng = result.x;

                            // Move the marker to the selected location
                            marker.setLatLng([lat, lng]);
                            map.setView([lat, lng], 15);

                            // Update the input fields with the selected coordinates
                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                            document.getElementById('location_search').value = result.label;

                            // Clear the suggestions box
                            suggestionsBox.innerHTML = '';
                        });
                        suggestionsBox.appendChild(suggestionItem);
                    });
                });
            });

            // Disable/enable the image upload field based on the selection of existing images
            const existingImageCheckboxes = document.querySelectorAll('.existing-image-checkbox');
            const galleryImageInput = document.getElementById('GalleryImage');

            existingImageCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (Array.from(existingImageCheckboxes).some(cb => cb.checked)) {
                        galleryImageInput.disabled = true;
                        galleryImageInput.required = false;
                    } else {
                        galleryImageInput.disabled = false;
                        galleryImageInput.required = true;
                    }
                });
            });
        });
    </script>
</body>

</html>
