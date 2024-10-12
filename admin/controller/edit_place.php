<?php 
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action)
{
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../../../Login.php');
    exit();
}

$place_id = intval($_GET['Place_ID']);

// Fetch the place details
$query = "SELECT * FROM places WHERE Place_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $place_id);
$stmt->execute();
$result = $stmt->get_result();
$place = $result->fetch_assoc();

// Fetch PlaceTypes from the database
$placeTypes = [];
$sql_place_types = "SELECT id, PlaceType FROM place_types";
$result_place_types = $conn->query($sql_place_types);
if ($result_place_types && $result_place_types->num_rows > 0) {
    while ($row = $result_place_types->fetch_assoc()) {
        $placeTypes[] = $row;
    }
}

if (!$place) {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to fetch details for Place ID $place_id.");
    echo "<script>alert('Place not found!'); window.location = '../manage_place.php';</script>";
    exit();
}

if (isset($_POST['submit'])) {
    $place_name = mysqli_real_escape_string($conn, $_POST['PlaceName']);
    $place_type = mysqli_real_escape_string($conn, $_POST['PlaceType']);
    $muni_id = intval($_POST['Muni_ID']);
    $description =  $_POST['PlaceDescription'];
    $location = mysqli_real_escape_string($conn, $_POST['PlaceLocation']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $place_picture = $place['PlacePicture']; // Default to the current image

    // Handle file upload if a new image is uploaded
    if (!empty($_FILES['PlacePicture']['name'])) {
        $file = $_FILES['PlacePicture'];
        $filename = $file['name'];
        $temp_path = $file['tmp_name'];
        $destination = "../../assets/images/ad-places/" . basename($filename);
        $filesize = $file['size'];

        $valid = true;

        // Validate file type and size
        if ($filesize > 5000000) { // 5MB limit
            $valid = false;
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Image upload failed: File size exceeds 5MB.');
            echo "<script>alert('Image file size exceeds 5MB.'); window.location = 'edit_place.php?Place_ID=$place_id';</script>";
            exit();
        }

        $file_extension = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
        $valid_extensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_extension, $valid_extensions)) {
            $valid = false;
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Image upload failed: Invalid file type.');
            echo "<script>alert('Invalid image file type. Only JPG, JPEG, and PNG are allowed.'); window.location = 'edit_place.php?Place_ID=$place_id';</script>";
            exit();
        }

        if ($valid) {
            if (move_uploaded_file($temp_path, $destination)) {
                $place_picture = basename($filename); // Store the new filename
            } else {
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to move uploaded file for Place ID $place_id.");
                echo "<script>alert('Failed to move uploaded file. Please check file permissions.'); window.location = 'edit_place.php?Place_ID=$place_id';</script>";
                exit();
            }
        }
    }

    // Update place details in the database
    $query = "UPDATE places SET PlaceName = ?, PlaceType = ?, Muni_ID = ?, PlaceDescription = ?, PlaceLocation = ?, Latitude = ?, Longitude = ?, PlacePicture = ? WHERE Place_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssisssssi', $place_name, $place_type, $muni_id, $description, $location, $latitude, $longitude, $place_picture, $place_id);

    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Successfully updated Place ID $place_id.");
        
        // Handle additional image uploads for the carousel
        if (isset($_FILES['PlacePictures']) && !empty($_FILES['PlacePictures']['tmp_name'][0])) {
            $valid_extensions = ['jpg', 'jpeg', 'png']; // Define valid extensions for carousel images

            foreach ($_FILES['PlacePictures']['tmp_name'] as $key => $tmp_name) {
                $carousel_filename = basename($_FILES['PlacePictures']['name'][$key]);
                $carousel_destination = "../../assets/images/ad-places/carousel/" . $carousel_filename;
                $carousel_filetype = strtolower(pathinfo($carousel_filename, PATHINFO_EXTENSION));

                if (in_array($carousel_filetype, $valid_extensions) && move_uploaded_file($tmp_name, $carousel_destination)) {
                    $sql_image = "INSERT INTO place_images (Place_ID, ImagePath) VALUES (?, ?)";
                    $stmt_image = $conn->prepare($sql_image);
                    $stmt_image->bind_param('is', $place_id, $carousel_filename);
                    $stmt_image->execute();
                } else {
                    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Carousel image upload failed for ' . $carousel_filename);
                }
            }
        }

        echo "<script>alert('Place updated successfully!'); window.location = '../manage_place.php';</script>";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to update Place ID $place_id: " . $stmt->error);
        echo "<script>alert('Failed to update place.'); window.location = 'edit_place.php?Place_ID=$place_id';</script>";
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Place</title>
    <link href="../css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch/dist/geosearch.css" />
</head>

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

<body>
    <div class="wrapper">
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <?php include('../includes/nav-place.php'); ?>
            </nav>
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Edit Place</h5>
                        </div>
                        <div class="card-body">
                            <form action="edit_place.php?Place_ID=<?php echo $place_id; ?>" method="POST" enctype="multipart/form-data">
                                <!-- Place Name -->
                                <div class="mb-3">
                                    <label for="PlaceName" class="form-label">Place Name</label>
                                    <input type="text" class="form-control" name="PlaceName" value="<?php echo htmlspecialchars($place['PlaceName']); ?>" required>
                                </div>
                                
                                <!-- Place Type -->
                                <div class="mb-3">
                                    <label for="PlaceType" class="form-label">Place Type</label>
                                    <select name="PlaceType" class="form-control" required>
                                        <?php foreach ($placeTypes as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['PlaceType']); ?>" <?php if($type['PlaceType'] == $place['PlaceType']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($type['PlaceType']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Municipality -->
                                <div class="mb-3">
                                    <label for="Muni_ID" class="form-label">Municipality</label>
                                    <select name="Muni_ID" class="form-control" required>
                                        <?php
                                        $municipalities_sql = "SELECT Muni_ID, MuniName FROM municipalities";
                                        $municipalities_result = mysqli_query($conn, $municipalities_sql);
                                        while ($municipality = mysqli_fetch_assoc($municipalities_result)) {
                                            $selected = $municipality['Muni_ID'] == $place['Muni_ID'] ? 'selected' : '';
                                            echo "<option value='" . $municipality['Muni_ID'] . "' $selected>" . htmlspecialchars($municipality['MuniName']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="PlaceDescription" class="form-label">Description</label>
                                    <textarea class="form-control" name="PlaceDescription" rows="4" required><?php echo htmlspecialchars($place['PlaceDescription']); ?></textarea>
                                </div>
                                
                                <!-- Location Search -->
                                <div class="mb-3">
                                    <label for="PlaceLocation" class="form-label">Search Place Location</label>
                                    <input type="text" class="form-control" id="location_search" name="PlaceLocation" value="<?php echo htmlspecialchars($place['PlaceLocation']); ?>" required>
                                    <div id="suggestions" class="suggestions-box"></div>
                                </div>
                                
                                <!-- Map for Latitude and Longitude -->
                                <div id="map"></div>
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" value="<?php echo htmlspecialchars($place['Latitude']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" value="<?php echo htmlspecialchars($place['Longitude']); ?>" required>
                                </div>
                                
                                <!-- Upload New Main Image -->
                                <div class="mb-3">
                                    <label for="PlacePicture" class="form-label">Upload New Main Image</label>
                                    <input type="file" class="form-control" id="PlacePicture" name="PlacePicture" accept="image/*">
                                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars(basename($place['PlacePicture'])); ?>">

                                    <div class="mt-2">
                                        <?php if (!empty($place['PlacePicture']) && $place['PlacePicture'] !== 'No image uploaded'): ?>
                                            <div>
                                                <img src="../../assets/images/ad-places/<?php echo htmlspecialchars(basename($place['PlacePicture'])); ?>" alt="Current Image" style="width: 100px; height: auto; display: block; margin-bottom: 10px;">
                                                <span>Current Image: <?php echo htmlspecialchars(basename($place['PlacePicture'])); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div style="color: gray;">
                                                <i class="bi bi-image" style="font-size: 24px;"></i> No image uploaded.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Upload Carousel Images -->
                                <div class="mb-3">
                                    <label for="PlacePictures" class="form-label">Upload Additional Images for Carousel</label>
                                    <input type="file" class="form-control" id="PlacePictures" name="PlacePictures[]" accept="image/*" multiple>

                                    <!-- Display existing carousel images -->
                                    <div class="mt-3">
                                        <h6>Current Carousel Images:</h6>
                                        <?php
                                        $carousel_sql = "SELECT ImagePath FROM place_images WHERE Place_ID = $place_id";
                                        $carousel_result = mysqli_query($conn, $carousel_sql);
                                        if ($carousel_result && mysqli_num_rows($carousel_result) > 0) {
                                            while ($carousel_image = mysqli_fetch_assoc($carousel_result)) {
                                                echo '<div style="margin-bottom: 10px;">';
                                                echo '<img src="../../assets/images/ad-places/carousel/' . htmlspecialchars($carousel_image['ImagePath']) . '" alt="Carousel Image" style="width: 100px; height: auto; display: block;">';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<p>No carousel images uploaded.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" name="submit" class="btn btn-primary">Update Place</button>
                                <a href="../manage_place.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../js/app.js"></script>


    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://unpkg.com/leaflet-geosearch/dist/geosearch.umd.js"></script>

    <script>
        tinymce.init({
            selector: '#PlaceDescription',
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });

        // Initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([<?php echo htmlspecialchars($place['Latitude']); ?>, <?php echo htmlspecialchars($place['Longitude']); ?>], 10);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a draggable marker to the map
            var marker = L.marker([<?php echo htmlspecialchars($place['Latitude']); ?>, <?php echo htmlspecialchars($place['Longitude']); ?>], {
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
