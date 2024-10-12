<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['activity_id'])) {
    $activity_id = intval($_GET['activity_id']);

    // Fetch activity details along with MuniName and category name
    $sql_activity = "
        SELECT a.*, c.name AS category_name, c.category_id, m.MuniName 
        FROM activities a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN municipalities m ON c.category_id = m.Muni_ID
        WHERE a.activity_id = ?";
    $stmt_activity = $conn->prepare($sql_activity);
    $stmt_activity->bind_param('i', $activity_id);
    $stmt_activity->execute();
    $result_activity = $stmt_activity->get_result();
    $activity = $result_activity->fetch_assoc();

    if (!$activity) {
        echo "Activity not found!";
        exit;
    }

    $stmt_activity->close();
} else {
    echo "No activity ID provided!";
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
    <title class="h"><?php echo htmlspecialchars($activity['name']); ?> - Activity Details</title>
    <style>
        /* Header Image Styling */
        .header-image {
            background: url('assets/images/uploads/activities/<?php echo htmlspecialchars($activity['image_url']); ?>') no-repeat center center/cover;
            height: 800px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            object-fit: cover;
            text-align: center;
            position: relative;
        }

        .header-image h2 {
            font-size: 48px;
            font-weight: bold;
            text-transform: uppercase;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
        }

        /* Main Content Styling */
        .main_activity_details {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .main_activity_details h4 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        .main_activity_details p {
            line-height: 1.8;
            font-size: 16px;
            color: #666;
        }

        /* Map Styling */
        #map {
            height: 400px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Breadcrumbs Styling */
        .breadcrumb {
            background-color: transparent;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: #666;
        }

        /* Coordinates styling */
        .coordinates {
            font-weight: bold;
            color: #28a745;
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
    </style>
</head>

<body>

    <header class="header_area">
        <div class="main_menu">
            <?php include 'includes/navbar.php' ?>
        </div>
    </header>

    <!-- Breadcrumbs Section -->
 

    <!-- Header Image Section -->
    <section class="header-image">
        <h2><?php echo htmlspecialchars($activity['name']); ?></h2>
    </section>

    <nav aria-label="breadcrumb" class="container mt-4">
  <ol class="breadcrumb" style="background-color: #f1f1f1; padding: 10px; border-radius: 8px;">
    <li class="breadcrumb-item">
      <a href="index.php" style="color: #379777;">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="all-categories.php" style="color: #379777;">Categories</a>
    </li>
    <li class="breadcrumb-item">
      <a href="activities.php?category_id=<?php echo $activity['category_id']; ?>" style="color: #379777;"><?php echo htmlspecialchars($activity['category_name']); ?></a>
    </li>
    <li class="breadcrumb-item active" aria-current="page" style="color: #45474B;"><?php echo htmlspecialchars($activity['name']); ?></li>
  </ol>
</nav>


    <!-- Main Activity Details Section -->
    <section class="activity-details section-margin">
        <div class="container">
            <div class="main_activity_details">
                <p class="text-muted"><?php echo htmlspecialchars($activity['location']); ?></p>
                <p><?php echo nl2br(($activity['description'])); ?></p>

                <!-- Map Section -->
                <div class="h1 pt-5 pb-3 text-success">Get Directions</div>
                <?php if (!empty($activity['latitude']) && !empty($activity['longitude'])): ?>
                    <div id="map"></div>
                    <p class="mt-3">Coordinates: 
                        <span class="coordinates">Latitude: <?php echo htmlspecialchars($activity['latitude']); ?></span>, 
                        <span class="coordinates">Longitude: <?php echo htmlspecialchars($activity['longitude']); ?></span>
                    </p>
                <?php endif; ?>
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
            <?php if (!empty($activity['latitude']) && !empty($activity['longitude'])): ?>
                var map = L.map('map').setView([<?php echo $activity['latitude']; ?>, <?php echo $activity['longitude']; ?>], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                var marker = L.marker([<?php echo $activity['latitude']; ?>, <?php echo $activity['longitude']; ?>]).addTo(map);

                marker.bindPopup("<b><?php echo htmlspecialchars($activity['name']); ?></b><br><a href='https://www.google.com/maps/dir/?api=1&destination=<?php echo $activity['latitude']; ?>,<?php echo $activity['longitude']; ?>' target='_blank'>Get Directions</a>").openPopup();
            <?php endif; ?>
        });
    </script>

</body>

</html>
