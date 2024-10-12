<?php
session_start();
include("../connection/connection.php");

// Get the municipality ID from the query string
$muni_id = intval($_GET['Muni_ID']);

// Fetch municipality details including the District ID
$sql = "SELECT * FROM municipalities WHERE Muni_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $muni_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Municipality not found.";
    exit();
}

$municipality = $result->fetch_assoc();
$district_id = $municipality['District_ID']; // Fetch the district ID of the current municipality
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($municipality['MuniName']); ?> - Municipality Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/style-2.css">
    <style>
        /* Municipality Header */
        .municipality-header {
            background-image: url('<?php echo htmlspecialchars($municipality['MuniPicture']); ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: 80vh;
            color: white;
            padding: 100px 0;
            text-align: center;
            text-shadow: 0px 4px 8px rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        /* Municipality Details Section */
        .municipality-details {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .municipality-details h5 {
            font-weight: bold;
            color: #379777;
        }

        .municipality-details p {
            color: #555;
            margin-bottom: 0;
        }

        .municipality-details i {
            color: #379777;
            font-size: 1.5rem;
            margin-right: 10px;
        }

        /* Tourism Style */
        .tourism-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .tourism-info .icon {
            margin-right: 15px;
        }

        .tourism-info .text-muted {
            font-size: 1.1rem;
        }

        /* Section Title */
        .section-title {
            color: #379777;
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* Map Styles */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .tourism-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tourism-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item a {
            color: #379777;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: #45474B;
        }
    </style>
</head>

<body data-bs-spy="scroll" data-bs-target=".navbar" data-bs-offset="70" class="bg-light text-dark">

    <!-- Municipality Header Section -->
    <div class="municipality-header">
        <h1 class="display-4 text-light"><?php echo htmlspecialchars($municipality['MuniName']); ?></h1>
    </div>

    <!-- Municipality Details Section -->
    <section id="about-municipality" class="py-5 bg-light">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item fs-5"><a href="../All-municipalities.php">Municipalities</a></li>
                    <li class="breadcrumb-item active fs-5 text-dark" aria-current="page"><?php echo htmlspecialchars($municipality['MuniName']); ?></li>
                </ol>
            </nav>

            <div class="row">
                <!-- Municipality Description -->
                <div class="col-lg-8">
                    <h2 class="section-title fs-1" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);">
                        Welcome to <?php echo htmlspecialchars($municipality['MuniName']); ?>
                    </h2>

                    <p class="lead text-muted"><?php echo nl2br(($municipality['MuniDesc'])); ?></p>
                </div>

                <!-- Municipality Info -->
                <div class="col-lg-4">
                    <div class="municipality-details">
                        <div class="tourism-info">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <h5>Established</h5>
                                <p><?php echo !empty($municipality['Established']) ? htmlspecialchars(date('Y', strtotime($municipality['Established']))) : 'Not Available'; ?></p>
                            </div>
                        </div>

                        <div class="tourism-info">
                            <i class="fas fa-user-tie"></i>
                            <div>
                                <h5>Mayor</h5>
                                <p><?php echo !empty($municipality['Mayor']) ? htmlspecialchars($municipality['Mayor']) : 'Not Available'; ?></p>
                            </div>
                        </div>

                        <div class="tourism-info">
                            <i class="fas fa-users"></i>
                            <div>
                                <h5>Population</h5>
                                <p><?php echo !empty($municipality['Population']) ? htmlspecialchars(number_format($municipality['Population'])) : 'Not Available'; ?></p>
                            </div>
                        </div>

                        <div class="tourism-info">
                            <i class="fas fa-map-marked-alt"></i>
                            <div>
                                <h5>Area</h5>
                                <p><?php echo !empty($municipality['Area']) ? htmlspecialchars($municipality['Area']) . ' km²' : 'Not Available'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section id="municipality-map" class="py-5 bg-white">
        <div class="container">
            <h2 class="py-3 fs-1">How to get there?</h2>
            <div id="map"></div>
        </div>
    </section>

    <!-- Tourism Places Section -->
    <section id="tourism-places" class="py-5 bg-dark">
        <div class="container">
            <div class="text-center mb-5">
                <h3 class="fs-6 text-uppercase text-success">Tourism</h3>
                <h2 class="fs-1 text-light">Places to Visit in <?php echo htmlspecialchars($municipality['MuniName']); ?></h2>
            </div>
            <div class="row" id="places-container">
                <!-- Places will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <!-- More like this Section -->
    <section id="Municipal-places" class="py-5 bg-light">
        <div class="container">
            <div class="text-start mb-5">
                <h3 class="fs-6 text-uppercase text-warning">More like this</h3>
                <h2 class="fs-1">Explore destinations within the district <?php echo htmlspecialchars($municipality['MuniName']); ?></h2>
            </div>
            <div class="row" id="Municipal-container">
                <!-- Municipalities will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            const muniId = <?php echo $muni_id; ?>;
            const districtId = <?php echo $district_id; ?>;

            fetchPlaces(muniId);
            fetchMunicipalitiesByDistrict(districtId, muniId);

            function fetchPlaces(muniId) {
                $.ajax({
                    url: '../ajax_content/fetch_places.php',
                    type: 'GET',
                    data: {
                        Muni_ID: muniId
                    },
                    success: function(response) {
                        $('#places-container').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ', status, error);
                        $('#places-container').html('<div class="text-center text-muted">Failed to load places.</div>');
                    }
                });
            }

            function fetchMunicipalitiesByDistrict(districtId, currentMuniId) {
                $.ajax({
                    url: '../ajax_content/fetch_municipalities_by_district.php',
                    type: 'GET',
                    data: {
                        district_id: districtId,
                        current_muni_id: currentMuniId
                    },
                    dataType: 'json',
                    success: function(municipalities) {
                        let municipalHtml = '';
                        if (municipalities.length > 0) {
                            municipalities.forEach(function(municipality) {
                                municipalHtml += `
                                    <div class="col-md-4 mb-4">
                                        <a href="municipality-details.php?Muni_ID=${municipality.Muni_ID}" class="card-link">
                                            <div class="card tourism-card border-0 h-100 text-white" style="border-radius: 15px; overflow: hidden;">
                                                <img src="${municipality.MuniPicture}" class="card-img" alt="${municipality.MuniName}">
                                                <div class="card-img-overlay d-flex flex-column justify-content-end p-3">
                                                    <h5 class="card-title mb-0 text-center">${municipality.MuniName}</h5>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                `;
                            });
                        } else {
                            municipalHtml = '<div class="col-12"><p class="text-center text-muted">No other municipalities found in this district.</p></div>';
                        }
                        $('#Municipal-container').html(municipalHtml);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ', status, error);
                        $('#Municipal-container').html('<div class="text-center text-muted">Failed to load municipalities.</div>');
                    }
                });
            }
        });

        // Initialize map with Leaflet
        var latitude = <?php echo isset($municipality['latitude']) ? floatval($municipality['latitude']) : '0'; ?>;
        var longitude = <?php echo isset($municipality['longitude']) ? floatval($municipality['longitude']) : '0'; ?>;
        var placeName = "<?php echo htmlspecialchars($municipality['MuniName'], ENT_QUOTES, 'UTF-8'); ?>";

        var map = L.map('map').setView([latitude, longitude], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.marker([latitude, longitude]).addTo(map)
            .bindPopup(`<b>${placeName}</b><br><br><a href="https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}" target="_blank">Get Directions</a>`)
            .openPopup();
    </script>

</body>

</html>