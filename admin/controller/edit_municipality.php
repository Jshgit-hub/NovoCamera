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

// Check if the user is logged in and is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../Login.php');
    exit();
}

$muni_id = intval($_GET['Muni_ID']);
$message = '';

if (isset($_POST['update_municipality'])) {
    $name = mysqli_real_escape_string($conn, $_POST['MuniName']);
    $description = mysqli_real_escape_string($conn, $_POST['MuniDesc']);
    $population = filter_var($_POST['Population'], FILTER_SANITIZE_NUMBER_INT);
    $area = filter_var($_POST['Area'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $established = mysqli_real_escape_string($conn, $_POST['Established']);
    $mayor = mysqli_real_escape_string($conn, $_POST['Mayor']);
    $location = mysqli_real_escape_string($conn, $_POST['Location']);
    $latitude = filter_var($_POST['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_var($_POST['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // File upload handling
    $file = $_FILES['MuniPicture'];
    $filename = $file['name'];
    $temp_path = $file['tmp_name'];
    $filesize = $file['size'];
    $upload_success = true;

    if (!empty($filename)) {
        // Generate a unique filename to avoid overwriting
        $filename = time() . "_" . basename($filename);
        $destination = "../../assets/images/Municipalities/" . $filename;

        // Validate file size and type
        $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
        $isValidFileType = in_array($fileExtension, $allowedFileTypes);
        $isValidFileSize = $filesize <= 5000000; // 5MB

        if ($isValidFileType && $isValidFileSize) {
            if (!move_uploaded_file($temp_path, $destination)) {
                $message = "Error uploading file.";
                $upload_success = false;
                logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to upload file for Municipality ID $muni_id: Error moving file.");
            }
        } else {
            $message = $isValidFileType ? 'File size exceeds 5MB.' : 'Invalid file type.';
            $upload_success = false;
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to upload file for Municipality ID $muni_id: $message");
        }
    } else {
        $destination = $_POST['existing_image'];
    }

    if ($upload_success) {
        // Prepare the SQL update statement
        $query = "UPDATE municipalities 
                  SET MuniName = ?, MuniDesc = ?, MuniPicture = ?, Population = ?, Area = ?, Established = ?, Mayor = ?, Location = ?, Latitude = ?, Longitude = ? 
                  WHERE Muni_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssdsdssddi', $name, $description, $destination, $population, $area, $established, $mayor, $location, $latitude, $longitude, $muni_id);
        
        if ($stmt->execute()) {
            $message = "Municipality updated successfully!";
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Updated Municipality ID $muni_id successfully.");
            header('Location: ../manage_municipality.php');
            exit();
        } else {
            $message = "Failed to update the municipality.";
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to update Municipality ID $muni_id: " . $stmt->error);
        }
    }
}

// Fetch the municipality data to populate the form
$query = "SELECT * FROM municipalities WHERE Muni_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $muni_id);
$stmt->execute();
$result = $stmt->get_result();
$municipality = $result->fetch_assoc();

if ($municipality) {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Fetched details for Municipality ID $muni_id.");
} else {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to fetch details for Municipality ID $muni_id.");
}

$latitude = !empty($municipality['latitude']) ? $municipality['latitude'] : '';
$longitude = !empty($municipality['longitude']) ? $municipality['longitude'] : '';
$imageName = !empty($municipality['MuniPicture']) ? basename($municipality['MuniPicture']) : 'No image uploaded';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Municipality</title>
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
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 text-light">Edit Municipality</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <form action="edit_municipality.php?Muni_ID=<?php echo $muni_id; ?>" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="MuniName" class="form-label">Municipality Name</label>
                                    <input type="text" class="form-control" name="MuniName" value="<?php echo htmlspecialchars($municipality['MuniName']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="MuniDesc" class="form-label">Municipality Description</label>
                                    <textarea class="form-control" id="MuniDesc" name="MuniDesc" rows="5" required><?php echo htmlspecialchars($municipality['MuniDesc']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="imageUpload" class="form-label">Upload New Image</label>
                                    <input type="file" class="form-control" id="imageUpload" name="MuniPicture" accept="image/*">
                                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($imageName); ?>">

                                    <div class="mt-2">
                                        <?php if (!empty($imageName) && $imageName !== 'No image uploaded'): ?>
                                            <div>
                                                <img src="../../assets/images/Municipalities/<?php echo htmlspecialchars($imageName); ?>" alt="Current Image" style="width: 100px; height: auto; display: block; margin-bottom: 10px;">
                                                <span>Current Image: <?php echo htmlspecialchars($imageName); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div style="color: gray;">
                                                <i class="bi bi-image" style="font-size: 24px;"></i> No image uploaded.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="Population" class="form-label">Population</label>
                                    <input type="text" class="form-control" name="Population" value="<?php echo htmlspecialchars($municipality['Population']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="Area" class="form-label">Area (in square kilometers)</label>
                                    <input type="text" class="form-control" name="Area" value="<?php echo htmlspecialchars($municipality['Area']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="Established" class="form-label">Established Date</label>
                                    <input type="date" class="form-control" name="Established" value="<?php echo htmlspecialchars($municipality['Established']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="Mayor" class="form-label">Mayor</label>
                                    <input type="text" class="form-control" name="Mayor" value="<?php echo htmlspecialchars($municipality['Mayor']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="Location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location_search" name="Location" value="<?php echo htmlspecialchars($municipality['Location']); ?>" required>
                                    <div id="suggestions" class="suggestions-box"></div>
                                </div>

                                <!-- Map for selecting location -->
                                <div id="map"></div>

                                <!-- Latitude and Longitude inputs -->
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" value="<?php echo htmlspecialchars(rtrim($latitude, '0')); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" value="<?php echo htmlspecialchars(rtrim($longitude, '0')); ?>" readonly>
                                </div>

                                <button type="submit" name="update_municipality" class="btn btn-primary">Update Municipality</button>
                                <a href="../manage_municipality.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../js/app.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-geosearch/dist/geosearch.umd.js"></script>
    <script>
        tinymce.init({
            selector: '#MuniDesc', // This should target the textarea
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });

        // Initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([<?php echo $latitude; ?>, <?php echo $longitude; ?>], 10); // Use coordinates from PHP

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a draggable marker to the map
            var marker = L.marker([<?php echo $latitude; ?>, <?php echo $longitude; ?>], {
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
    </script>
</body>

</html>
