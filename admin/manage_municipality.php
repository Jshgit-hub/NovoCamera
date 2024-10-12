<?php
session_start();
include '../connection/connection.php';

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../Login.php');
    exit();
}

// Handle deletion of a municipality
if (isset($_POST['delete_municipality'])) {
    $muni_id = intval($_POST['muni_id']);
    $query = "DELETE FROM municipalities WHERE Muni_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $muni_id);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Deleted Municipality ID $muni_id successfully.");
        $_SESSION['message'] = "Municipality deleted successfully!";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Failed to delete Municipality ID $muni_id.");
        $_SESSION['message'] = "Failed to delete the municipality.";
    }
    header('Location: manage_municipality.php');
    exit();
}

// Initialize search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch municipalities based on search query or fetch all if no search query is provided
$municipalities = [];
if (!empty($search_query)) {
    $sql = "SELECT * FROM municipalities WHERE MuniName LIKE ? OR Mayor LIKE ? OR Location LIKE ? ORDER BY LOWER(MuniName) ASC";
    $stmt = $conn->prepare($sql);
    $search_term = '%' . $search_query . '%';
    $stmt->bind_param('sss', $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $municipalities = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $sql = "SELECT * FROM municipalities ORDER BY LOWER(MuniName) ASC";
    $result = $conn->query($sql);
    $municipalities = $result->fetch_all(MYSQLI_ASSOC);
}

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage Municipalities">
    <link href="css/app.css" rel="stylesheet">
    <title>Manage Municipalities</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                            <h5 class="card-title mb-0">Manage Municipalities</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Form -->
                            <form class="mb-3">
                                <div class="input-group">
                                    <input type="text" id="search" class="form-control" placeholder="Search by name, mayor, or location">
                                </div>
                            </form>

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
                                        <th>Population</th>
                                        <th>Area (sq km)</th>
                                        <th>Mayor</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="municipality-table">
                                    <!-- AJAX response will populate this area -->
                                </tbody>
                            </table>

                            <p id="no-results" style="display: none;">No municipalities found.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script>
        $(document).ready(function() {
            // Function to load data
            function load_data(query) {
                $.ajax({
                    url: "ajax_admin/search_municipalities.php",
                    method: "POST",
                    data: {search: query},
                    success: function(data) {
                        $('#municipality-table').html(data);
                        if (data.trim() === "") {
                            $('#no-results').show();
                        } else {
                            $('#no-results').hide();
                        }
                    }
                });
            }

            // Load all data initially
            load_data('');

            // Trigger AJAX call on keyup in the search box
            $('#search').on('keyup', function() {
                var query = $(this).val();
                load_data(query);
            });
        });
    </script>
</body>

</html>
