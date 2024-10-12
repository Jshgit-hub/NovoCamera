<?php 
include 'includes/ActivitiesBackend.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage Activities">
    <link href="css/app.css" rel="stylesheet">
    <title>Manage Activities</title>
    <!-- Include TinyMCE -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
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
                            <h5 class="card-title mb-0">Manage Activities</h5>
                        </div>
                        <div class="card-body">
                            <!-- Add Activity Form -->
                            <form method="POST" action="manage-activities.php" class="mb-3" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Activity Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="location">Location</label>
                                            <input type="text" class="form-control" id="location_search" name="location" required>
                                            <div id="suggestions" class="suggestions-box"></div>
                                        </div>
                                        <div id="map"></div>
                                        <div class="mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="longitude" class="form-label">Longitude</label>
                                            <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control tinymce" id="description" name="description" rows="6"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="category_id">Category</label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="image_url">Image (Optional)</label>
                                            <input type="file" class="form-control" id="image_url" name="image_url" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="add_activity" class="btn btn-primary mt-3">Add Activity</button>
                            </form>

                            <!-- Manage Existing Activities -->
                            <hr>
                            <!-- Filter and Search Form -->
                            <form method="GET" action="manage-activities.php" class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or location" value="<?= htmlspecialchars($search_term) ?>">
                                </div>
                                <div class="col-md-4">
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>" <?= $category_filter == $category['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                            </form>

                             <div class="mb-2">
                                <strong><?= $total_activities ?> activities found</strong>
                                <?php if ($search_term || $category_filter): ?>
                                    (filtered by <?= $category_filter ? 'category: ' . $category_filter : '' ?><?= $search_term ? ($category_filter ? ' and ' : '') . 'search: "' . $search_term . '"' : '' ?>)
                                <?php endif; ?>
                            </div>

                            <h5 class="mt-4">Existing Activities</h5>
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info">
                                    <?php
                                    echo $_SESSION['message'];
                                    unset($_SESSION['message']);
                                    ?>
                                </div>
                            <?php endif; ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Category</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['location']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['latitude']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['longitude']); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editActivityModal<?php echo $activity['activity_id']; ?>">Edit</a>
                                                <form action="manage-activities.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="activity_id" value="<?php echo $activity['activity_id']; ?>">
                                                    <button type="submit" name="delete_activity" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this activity?');">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <!-- Edit Activity Modal -->
                                        <div class="modal fade" id="editActivityModal<?php echo $activity['activity_id']; ?>" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editActivityModalLabel">Edit Activity</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="manage-activities.php" enctype="multipart/form-data">
                                                            <input type="hidden" name="activity_id" value="<?php echo $activity['activity_id']; ?>">
                                                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($activity['image_url']); ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="name">Activity Name</label>
                                                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($activity['name']); ?>" required>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="location">Location</label>
                                                                        <input type="text" class="form-control" id="location_search_<?php echo $activity['activity_id']; ?>" name="location" value="<?php echo htmlspecialchars($activity['location']); ?>" required>
                                                                        <div id="suggestions_<?php echo $activity['activity_id']; ?>" class="suggestions-box"></div>
                                                                        <!-- Map for editing activity -->
                                                                        <div id="map_<?php echo $activity['activity_id']; ?>" class="map" style="height: 400px; width: 100%; margin-top: 20px;"></div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="latitude" class="form-label">Latitude</label>
                                                                        <input type="text" class="form-control" id="latitude_<?php echo $activity['activity_id']; ?>" name="latitude" value="<?php echo htmlspecialchars($activity['latitude']); ?>" readonly>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="longitude" class="form-label">Longitude</label>
                                                                        <input type="text" class="form-control" id="longitude_<?php echo $activity['activity_id']; ?>" name="longitude" value="<?php echo htmlspecialchars($activity['longitude']); ?>" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="description">Description</label>
                                                                        <textarea class="form-control tinymce" id="description_<?php echo $activity['activity_id']; ?>" name="description" rows="6" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="category_id">Category</label>
                                                                        <select class="form-control" id="category_id_<?php echo $activity['activity_id']; ?>" name="category_id" required>
                                                                            <?php foreach ($categories as $category): ?>
                                                                                <option value="<?php echo $category['category_id']; ?>" <?php echo $activity['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="image_url">Image (Optional)</label>
                                                                        <input type="file" class="form-control" id="image_url" name="image_url" accept="image/*">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="submit" name="edit_activity" class="btn btn-primary mt-3">Update Activity</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (empty($activities)): ?>
                                <p>No activities found.</p>
                            <?php endif; ?>

                            <!-- Pagination Controls -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Button -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search_term ?>&category=<?= $category_filter ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= $search_term ?>&category=<?= $category_filter ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search_term ?>&category=<?= $category_filter ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
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
            selector: '.tinymce',
            plugins: 'lists link image preview',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | link image',
            branding: false,
            content_style: "body { font-family: 'Poppins', sans-serif; font-size: 14px; color: #333; }"
        });

        // Initialize the map for adding a new activity
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

        // Repeat the map initialization for each edit activity modal
        <?php foreach ($activities as $activity): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var mapEdit<?php echo $activity['activity_id']; ?> = L.map('map_<?php echo $activity['activity_id']; ?>').setView([<?php echo $activity['latitude']; ?>, <?php echo $activity['longitude']; ?>], 10);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapEdit<?php echo $activity['activity_id']; ?>);

                // Add a draggable marker to the map
                var markerEdit<?php echo $activity['activity_id']; ?> = L.marker([<?php echo $activity['latitude']; ?>, <?php echo $activity['longitude']; ?>], {
                    draggable: true
                }).addTo(mapEdit<?php echo $activity['activity_id']; ?>);

                // Event listener to update the form inputs with the marker's current location
                markerEdit<?php echo $activity['activity_id']; ?>.on('dragend', function(e) {
                    var lat = markerEdit<?php echo $activity['activity_id']; ?>.getLatLng().lat;
                    var lng = markerEdit<?php echo $activity['activity_id']; ?>.getLatLng().lng;
                    document.getElementById('latitude_<?php echo $activity['activity_id']; ?>').value = lat;
                    document.getElementById('longitude_<?php echo $activity['activity_id']; ?>').value = lng;
                });

                // Set up the geosearch provider for the edit modal
                const providerEdit<?php echo $activity['activity_id']; ?> = new window.GeoSearch.OpenStreetMapProvider();

                // Listen for input in the search box in the edit modal
                let searchTimeoutEdit<?php echo $activity['activity_id']; ?>;
                document.getElementById('location_search_<?php echo $activity['activity_id']; ?>').addEventListener('input', function(e) {
                    const query = e.target.value;
                    const suggestionsBoxEdit<?php echo $activity['activity_id']; ?> = document.getElementById('suggestions_<?php echo $activity['activity_id']; ?>');

                    // Delay the search to avoid too many API calls
                    clearTimeout(searchTimeoutEdit<?php echo $activity['activity_id']; ?>);
                    searchTimeoutEdit<?php echo $activity['activity_id']; ?> = setTimeout(async function() {
                        if (query.length > 2) { // Start searching after the user has typed 3 characters
                            const results = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                                .then(response => response.json());

                            suggestionsBoxEdit<?php echo $activity['activity_id']; ?>.innerHTML = ''; // Clear previous suggestions

                            if (results && results.length > 0) {
                                results.forEach(result => {
                                    const suggestionItemEdit = document.createElement('div');
                                    suggestionItemEdit.textContent = result.display_name;
                                    suggestionItemEdit.addEventListener('click', function() {
                                        // Update marker position and map view
                                        const lat = result.lat;
                                        const lng = result.lon;
                                        markerEdit<?php echo $activity['activity_id']; ?>.setLatLng([lat, lng]);
                                        mapEdit<?php echo $activity['activity_id']; ?>.setView([lat, lng], 15);

                                        // Update form inputs
                                        document.getElementById('latitude_<?php echo $activity['activity_id']; ?>').value = lat;
                                        document.getElementById('longitude_<?php echo $activity['activity_id']; ?>').value = lng;

                                        // Set the location search box value
                                        document.getElementById('location_search_<?php echo $activity['activity_id']; ?>').value = result.display_name;

                                        // Clear suggestions
                                        suggestionsBoxEdit<?php echo $activity['activity_id']; ?>.innerHTML = '';
                                    });
                                    suggestionsBoxEdit<?php echo $activity['activity_id']; ?>.appendChild(suggestionItemEdit);
                                });
                            }
                        } else {
                            suggestionsBoxEdit<?php echo $activity['activity_id']; ?>.innerHTML = ''; // Clear suggestions if query is too short
                        }
                    }, 300); // 300ms delay for search
                });

                // Hide suggestions box when clicking outside
                document.addEventListener('click', function(e) {
                    if (!document.getElementById('location_search_<?php echo $activity['activity_id']; ?>').contains(e.target)) {
                        document.getElementById('suggestions_<?php echo $activity['activity_id']; ?>').innerHTML = '';
                    }
                });
            });
        <?php endforeach; ?>
    </script>
</body>

</html>