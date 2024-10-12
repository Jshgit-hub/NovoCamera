<?php
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');
session_start();

// Function to calculate percentage change
function calculatePercentageChange($current, $previous)
{
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return round((($current - $previous) / $previous) * 100, 2);
}

// Fetch total counts and previous counts
$total_users_query = "SELECT COUNT(*) as total_users FROM users";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

$total_municipalities_query = "SELECT COUNT(*) as total_municipalities FROM municipalities";
$total_municipalities_result = mysqli_query($conn, $total_municipalities_query);
$total_municipalities = mysqli_fetch_assoc($total_municipalities_result)['total_municipalities'];

$total_places_query = "SELECT COUNT(*) as total_places FROM places";
$total_places_result = mysqli_query($conn, $total_places_query);
$total_places = mysqli_fetch_assoc($total_places_result)['total_places'];

$total_blogs_query = "SELECT COUNT(*) as total_blogs FROM blogs";
$total_blogs_result = mysqli_query($conn, $total_blogs_query);
$total_blogs = mysqli_fetch_assoc($total_blogs_result)['total_blogs'];

$total_activities_query = "SELECT COUNT(*) as total_activities FROM activities";
$total_activities_result = mysqli_query($conn, $total_activities_query);
$total_activities = mysqli_fetch_assoc($total_activities_result)['total_activities'];

$total_admins_query = "SELECT COUNT(*) as total_admins FROM users WHERE role='admin'";
$total_admins_result = mysqli_query($conn, $total_admins_query);
$total_admins = mysqli_fetch_assoc($total_admins_result)['total_admins'];

$new_users_query = "SELECT COUNT(*) as new_users FROM users WHERE MONTH(Date_created) = MONTH(NOW()) AND YEAR(Date_created) = YEAR(NOW())";
$new_users_result = mysqli_query($conn, $new_users_query);
$new_users = mysqli_fetch_assoc($new_users_result)['new_users'];

// Mock previous counts for change calculation
$previous_users = $total_users - 0;
$previous_municipalities = $total_municipalities - 0;
$previous_places = $total_places - 0;
$previous_blogs = $total_blogs - 0;
$previous_activities = $total_activities - 0;

// Calculate percentage changes
$user_change = calculatePercentageChange($total_users, $previous_users);
$municipality_change = calculatePercentageChange($total_municipalities, $previous_municipalities);
$place_change = calculatePercentageChange($total_places, $previous_places);
$blog_change = calculatePercentageChange($total_blogs, $previous_blogs);
$activity_change = calculatePercentageChange($total_activities, $previous_activities);

// Fetch municipalities with coordinates
$municipalities = [];
$sql = "SELECT MuniName, latitude, longitude FROM municipalities WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
$result = $conn->query($sql);
if ($result) {
    $municipalities = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch all places for the map
$all_places = [];
$sql = "SELECT p.*, m.MuniName FROM places p LEFT JOIN municipalities m ON p.Muni_ID = m.Muni_ID";
$result = $conn->query($sql);
if ($result) {
    $all_places = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch top places
$top_places = [];
$sql = "SELECT p.*, m.MuniName FROM places p LEFT JOIN municipalities m ON p.Muni_ID = m.Muni_ID WHERE p.top_place = 1";
$result = $conn->query($sql);
if ($result) {
    $top_places = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch activities
$activities = [];
$sql = "SELECT * FROM activities";
$result = $conn->query($sql);
if ($result) {
    $activities = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch blogs
$blogs = [];
$sql = "SELECT * FROM blogs WHERE is_top_blog = 1";
$result = $conn->query($sql);
if ($result) {
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">

    <link href="css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        #map {
            height: 350px;
        }

        .filter-buttons {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include("includes/adminsidebar.php") ?>

        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>

                <?php include('includes/navbar-top.php') ?>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <h1 class="h3 mb-3"><strong>Analytics</strong> Dashboard</h1>

                    <div class="row">
                        <!-- Top row of cards -->
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Users</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $total_users; ?></h1>
                                            <div class="mb-0">
                                                <span class="<?php echo $user_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <i class="mdi mdi-arrow-<?php echo $user_change >= 0 ? 'top-right' : 'bottom-right'; ?>"></i>
                                                    <?php echo $user_change; ?>%
                                                </span>
                                                <span class="text-muted">Since last month</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Municipalities</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $total_municipalities; ?></h1>
                                            <div class="mb-0">
                                                <span class="<?php echo $municipality_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <i class="mdi mdi-arrow-<?php echo $municipality_change >= 0 ? 'top-right' : 'bottom-right'; ?>"></i>
                                                    <?php echo $municipality_change; ?>%
                                                </span>
                                                <span class="text-muted">Since last month</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="map-pin"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Posted Blogs</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $total_blogs; ?></h1>
                                            <div class="mb-0">
                                                <span class="<?php echo $blog_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <i class="mdi mdi-arrow-<?php echo $blog_change >= 0 ? 'top-right' : 'bottom-right'; ?>"></i>
                                                    <?php echo $blog_change; ?>%
                                                </span>
                                                <span class="text-muted">Since last month</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="book-open"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Posted Activities Card -->
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Posted Activities</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $total_activities; ?></h1>
                                            <div class="mb-0">
                                                <span class="<?php echo $activity_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <i class="mdi mdi-arrow-<?php echo $activity_change >= 0 ? 'top-right' : 'bottom-right'; ?>"></i>
                                                    <?php echo $activity_change; ?>%
                                                </span>
                                                <span class="text-muted">Since last month</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Second row of cards -->
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Total Posted Places</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $total_places; ?></h1>
                                            <div class="mb-0">
                                                <span class="<?php echo $place_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <i class="mdi mdi-arrow-<?php echo $place_change >= 0 ? 'top-right' : 'bottom-right'; ?>"></i>
                                                    <?php echo $place_change; ?>%
                                                </span>
                                                <span class="text-muted">Since last month</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="map-pin"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Admins Card -->
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Total Admin</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $total_admins; ?></h1>
                                            <div class="mb-0">
                                                <span class="text-success">
                                                    <i class="mdi mdi-arrow-top-right"></i> 8.12%
                                                </span>
                                                <span class="text-muted">Since last week</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="user-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Users Card -->
                        <div class="col-xl-3 col-xxl-3 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">New User</h5>
                                            <h1 class="mt-1 mb-3"><?php echo $new_users; ?></h1>
                                            <div class="mb-0">
                                                <span class="text-danger">
                                                    <i class="mdi mdi-arrow-bottom-right"></i> -1.42%
                                                </span>
                                                <span class="text-muted">Since last month</span>
                                            </div>
                                        </div>
                                        <div class="stat text-primary">
                                            <i class="align-middle" data-feather="activity"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    
                    </div>

                    <!-- Real-Time Map and Calendar -->
                    <div class="row">
                        <div class="col-12 col-md-8 col-xxl-6 d-flex order-1 order-xxl-2 w-100">
                            <div class="card flex-fill w-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Real-Time Map</h5>
                                </div>
                                <div class="card-body px-4">
                                    <div id="map" style="height: 500px;"></div>
                                    <!-- Filter buttons --> 
                                    <div class="filter-buttons">
                                        <button id="showMunicipalities" class="btn btn-primary ">Show Municipalities</button>
                                        <button id="showPlaces" class="btn btn-secondary">Show Places</button>
                                        <button id="showActivities" class="btn btn-success">Show Activities</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Places and Top Blogs -->
                    <div class="row">

                    </div>

                    <!-- Top Places -->
                    <div class="col-12 col-lg-8 col-xxl-9 w-100 d-flex">
                        <div class="card flex-fill border-primary shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0" style="font-size: 1.5rem; font-weight: bold; cursor: pointer;">
                                    Top Places
                                </h5>
                            </div>
                            <table class="table table-striped table-bordered my-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th class="d-none d-xl-table-cell">Name</th>
                                        <th class="d-none d-xl-table-cell">Place type</th>
                                        <th>Location</th>
                                        <th class="d-none d-md-table-cell">Municipalities</th>
                                        <th class="d-none d-md-table-cell">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_places as $place): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($place['Place_ID']); ?></td>
                                            <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($place['PlaceName']); ?></td>
                                            <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($place['PlaceType']); ?></td>
                                            <td><?php echo htmlspecialchars($place['PlaceLocation']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($place['MuniName']); ?></td>
                                            <td class="d-none d-md-table-cell">
                                                <a href="#" class="btn btn-primary btn-sm me-2">Edit</a>
                                                <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Blogs -->
                    <div class="col-12 col-lg-8 col-xxl-9 w-100 d-flex">
                        <div class="card flex-fill border-info shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0" style="font-size: 1.5rem; font-weight: bold; cursor: pointer;">
                                    Top Blogs
                                </h5>
                            </div>
                            <table class="table table-hover my-0">
                                <thead class="table-info">
                                    <tr>
                                        <th>Title</th>
                                        <th class="d-none d-xl-table-cell">Author</th>
                                        <th class="d-none d-xl-table-cell">Status</th>
                                        <th>Views</th>
                                        <th class="d-none d-md-table-cell">Created At</th>
                                        <th class="d-none d-md-table-cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogs as $blog): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                            <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($blog['author']); ?></td>
                                            <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($blog['status']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['views']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($blog['created_at']); ?></td>
                                            <td class="d-none d-md-table-cell">
                                                <a href="#" class="btn btn-info btn-sm me-2">Edit</a>
                                                <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <style>
                        .card-title:hover {
                            color: #f0ad4e;
                            transition: color 0.3s ease;
                        }
                    </style>

                    <script src="js/app.js"></script>
                    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Initialize the map
                            var map = L.map('map').setView([15.5, 121.0], 10);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 18,
                                attribution: '© OpenStreetMap contributors'
                            }).addTo(map);

                            var municipalityMarkers = [];
                            var placeMarkers = [];
                            var activityMarkers = [];
                            var blogMarkers = [];

                            // Add markers for municipalities
                            <?php foreach ($municipalities as $municipality): ?>
                                var marker = L.marker([<?php echo $municipality['latitude']; ?>, <?php echo $municipality['longitude']; ?>])
                                    .bindPopup("<?php echo htmlspecialchars($municipality['MuniName']); ?>");
                                municipalityMarkers.push(marker);
                                marker.addTo(map);
                            <?php endforeach; ?>

                            // Add markers for places
                            <?php foreach ($all_places as $place): ?>
                                var marker = L.marker([<?php echo $place['Latitude']; ?>, <?php echo $place['Longitude']; ?>])
                                    .bindPopup("<?php echo htmlspecialchars($place['PlaceName']); ?>");
                                placeMarkers.push(marker);
                            <?php endforeach; ?>

                            // Add markers for activities
                            <?php foreach ($activities as $activity): ?>
                                var marker = L.marker([<?php echo $activity['latitude']; ?>, <?php echo $activity['longitude']; ?>])
                                    .bindPopup("<?php echo htmlspecialchars($activity['name']); ?>");
                                activityMarkers.push(marker);
                            <?php endforeach; ?>

                            // Add markers for blogs
                            <?php foreach ($blogs as $blog): ?>
                                var marker = L.marker([<?php echo $blog['latitude']; ?>, <?php echo $blog['longitude']; ?>])
                                    .bindPopup("<?php echo htmlspecialchars($blog['title']); ?>");
                                blogMarkers.push(marker);
                            <?php endforeach; ?>

                            // Clear markers function
                            function clearMarkers(markerArray) {
                                markerArray.forEach(marker => {
                                    map.removeLayer(marker);
                                });
                            }

                            // Show municipalities button
                            document.getElementById('showMunicipalities').addEventListener('click', function() {
                                clearMarkers(placeMarkers);
                                clearMarkers(activityMarkers);
                                clearMarkers(blogMarkers);
                                municipalityMarkers.forEach(marker => {
                                    marker.addTo(map);
                                });
                            });

                            // Show places button
                            document.getElementById('showPlaces').addEventListener('click', function() {
                                clearMarkers(municipalityMarkers);
                                clearMarkers(activityMarkers);
                                clearMarkers(blogMarkers);
                                placeMarkers.forEach(marker => {
                                    marker.addTo(map);
                                });
                            });

                            // Show activities button
                            document.getElementById('showActivities').addEventListener('click', function() {
                                clearMarkers(municipalityMarkers);
                                clearMarkers(placeMarkers);
                                clearMarkers(blogMarkers);
                                activityMarkers.forEach(marker => {
                                    marker.addTo(map);
                                });
                            });

                            // Show blogs button
                            document.getElementById('showBlogs').addEventListener('click', function() {
                                clearMarkers(municipalityMarkers);
                                clearMarkers(placeMarkers);
                                clearMarkers(activityMarkers);
                                blogMarkers.forEach(marker => {
                                    marker.addTo(map);
                                });
                            });
                        });
                    </script>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var date = new Date(Date.now() - 5 * 24 * 60 * 60 * 1000);
                            var defaultDate = date.getUTCFullYear() + "-" + (date.getUTCMonth() + 1) + "-" + date.getUTCDate();
                            document.getElementById("datetimepicker-dashboard").flatpickr({
                                inline: true,
                                prevArrow: "<span title=\"Previous month\">&laquo;</span>",
                                nextArrow: "<span title=\"Next month\">&raquo;</span>",
                                defaultDate: defaultDate
                            });
                        });
                    </script>

</body>

</html>