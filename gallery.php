<?php
session_start();
// Database connection
include 'connection/connection.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isLoggedIn = isset($_SESSION['username']);

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
    <title>Interactive Tourism Gallery</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-2.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F7F8;
            overflow-x: hidden;
        }

        .filter-card {
            margin-bottom: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 20px;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .filter-row .form-select,
        .filter-row input {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 48%;
        }

        .filter-btn {
            background-color: #379777;
            color: #fff;
            border-radius: 30px;
            padding: 12px 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            margin-top: 15px;
        }

        .filter-btn:hover {
            background-color: #2e8061;
            transform: scale(1.05);
        }

        .gallery-item {
            margin-bottom: 20px;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .gallery-item.show {
            opacity: 1;
            transform: scale(1);
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.5s ease, box-shadow 0.5s ease;
            cursor: pointer;
            position: relative;
        }

        .card:hover {
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

        .view-content-btn {
            margin-top: 10px;
            background-color: #379777;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }

        .view-content-btn:hover {
            background-color: #2e8061;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .pagination .page-item .page-link {
            border: none;
            background-color: #379777;
            color: white;
            margin: 0 5px;
            border-radius: 25px;
        }

        .pagination .page-item.active .page-link {
            background-color: #45474B;
            color: white;
        }

        .pagination .page-item .page-link:hover {
            background-color: #2e8061;
            color: white;
        }

        /* Responsive for Mobile */
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }

            .filter-btn {
                width: 100%;
                margin-top: 10px;
            }

            .card-img-top {
                height: 200px;
            }

            .card {
                margin-bottom: 20px;
            }
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
                <h5 class="card-title text-success fs-2">Find Your Destination</h5>

                <!-- Filter Row: Municipality, Date Range, Year, and Sort by Date -->
                <div class="filter-row">
                    <!-- Municipality Dropdown -->
                    <select id="municipality" class="form-select text-success ">
                        <option value="all">All Municipalities</option>
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?php echo htmlspecialchars($municipality['Muni_ID']); ?>">
                                <?php echo htmlspecialchars($municipality['MuniName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Year Filter -->
                    <select id="year-filter" class="form-select">
                        <option value="all">All Years</option>
                        <?php
                        $currentYear = date("Y");
                        for ($year = 2024; $year <= $currentYear + 10; $year++) {  // Show future years
                            echo "<option value='{$year}'>{$year}</option>";
                        }
                        ?>
                    </select>

                    <!-- Date Range Filter -->
                    <div class="input-group pt-3" style="width: 48%;">
                        <span class="input-group-text">From</span>
                        <input type="date" id="start-date" class="form-control" min="2024-01-01">
                        <span class="input-group-text">To</span>
                        <input type="date" id="end-date" class="form-control" min="2024-01-01">
                    </div>
                </div>

                <button class="filter-btn" id="apply-filter">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
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

        <!-- Modal for Image View -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel">Image View</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img id="modalImage" src="" class="img-fluid w-100" alt="Fullscreen Image">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php' ?>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="vendors/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script>
        let currentPage = 1;
        const itemsPerPage = 20;
        const municipalitySelect = document.getElementById('municipality');
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const yearSelect = document.getElementById('year-filter');
        const sortDate = document.getElementById('sort-date');
        const sortOrderText = document.getElementById('sort-order');
        let sortOrder = 'DESC'; // Default sorting order (newest)

        const gallery = document.getElementById('gallery');

        // Load content immediately when the page is first loaded
        document.addEventListener('DOMContentLoaded', fetchPlaces);

        // Add event listeners for filters and sorting
        municipalitySelect.addEventListener('change', fetchPlaces);
        startDateInput.addEventListener('change', fetchPlaces);
        endDateInput.addEventListener('change', fetchPlaces);
        yearSelect.addEventListener('change', fetchPlaces);

        // Fetch places with AJAX
        function fetchPlaces() {
            // Show loading spinner
            document.getElementById('loading-spinner').style.display = 'block';

            $.ajax({
                url: 'ajax_content/load_gallery.php',
                method: 'GET',
                data: {
                    municipality: municipalitySelect.value,
                    start_date: startDateInput.value,
                    end_date: endDateInput.value,
                    year: yearSelect.value,
                    sort_order: sortOrder
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

        // Display gallery cards with content links or no content message
        function displayGallery(items) {
            gallery.innerHTML = ''; // Clear existing gallery

            if (items.length === 0) {
                gallery.innerHTML = '<p class="text-center text-muted">No results found.</p>';
                return;
            }

            items.forEach(item => {
                const yearCreated = new Date(item.created_at).getFullYear();
                const viewContentButton = item.Place_ID ?
                    `<a href="place-details.php?Place_ID=${item.Place_ID}" class="view-content-btn">View Content</a>` :
                    item.post_id ?
                    `<a href="user-post-details.php?post_id=${item.post_id}" class="view-content-btn">View Post</a>` :
                    ''; // No content button if neither is present

                const galleryCard = `
                    <div class="col-lg-4 col-md-6 col-sm-12 gallery-item show">
                        <div class="card" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="assets/images/gallery/${item.image_path}">
                            <img src="assets/images/gallery/${item.image_path}" class="card-img-top" alt="${item.title}">
                            <div class="overlay">
                                <h5>${item.title}</h5>
                                <p>${item.MuniName} - ${yearCreated}</p>
                                ${viewContentButton}
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

            // Event listener to open the modal with the image
            $('#imageModal').on('show.bs.modal', function(event) {
                const card = $(event.relatedTarget); // Get the clicked card
                const imageUrl = card.data('image'); // Extract the image URL
                $('#modalImage').attr('src', imageUrl); // Set the image in the modal
            });
        }

        // Initial load
        fetchPlaces();
    </script>
</body>

</html>