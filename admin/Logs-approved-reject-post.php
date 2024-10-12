<?php
session_start();
include '../connection/connection.php';

// Initialize the search query
$searchQuery = "";
if (isset($_GET['search'])) {
    $searchQuery = mysqli_real_escape_string($conn, $_GET['search']);
}

// Fetch approved posts from the database based on the status and search query
$approvedQuery = "SELECT p.post_id, p.title, p.image_url, p.place_type, m.MuniName, p.created_at
                  FROM post p
                  JOIN municipalities m ON p.Muni_ID = m.Muni_ID
                  WHERE p.status = 'approved' AND (p.title LIKE '%$searchQuery%' OR m.MuniName LIKE '%$searchQuery%')
                  ORDER BY p.created_at DESC";
$approvedResult = mysqli_query($conn, $approvedQuery);

// Fetch rejected posts from the database based on the status and search query
$rejectedQuery = "SELECT p.post_id, p.title, p.image_url, p.place_type, m.MuniName, p.reason_for_rejection, p.created_at
                  FROM post p
                  JOIN municipalities m ON p.Muni_ID = m.Muni_ID
                  WHERE p.status = 'rejected' AND (p.title LIKE '%$searchQuery%' OR m.MuniName LIKE '%$searchQuery%')
                  ORDER BY p.created_at DESC";
$rejectedResult = mysqli_query($conn, $rejectedQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
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
                    <!-- Search Form -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Approved and Rejected Posts</h5>
                            <form method="GET" action="" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Search by title or municipality" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </form>
                        </div>

                        <!-- Approved Posts Section -->
                        <div class="table-responsive">
                            <h6 class="p-3 bg-success text-white mb-0">Approved Posts</h6>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Post ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>Place Type</th>
                                        <th>Municipality</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if (mysqli_num_rows($approvedResult) > 0) {
                                            while ($row = mysqli_fetch_assoc($approvedResult)) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['post_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                                echo "<td><img src='" . htmlspecialchars($row['image_url']) . "' alt='Image' width='50' height='50'></td>";
                                                echo "<td>" . htmlspecialchars($row['place_type']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['MuniName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                                echo "<td><a href='action/view_post.php?id=" . htmlspecialchars($row['post_id']) . "' class='btn btn-sm btn-outline-primary me-1'>View</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>No approved posts available.</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Rejected Posts Section -->
                        <div class="table-responsive">
                            <h6 class="p-3 bg-danger text-white mb-0">Rejected Posts</h6>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Post ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>Place Type</th>
                                        <th>Municipality</th>
                                        <th>Created At</th>
                                        <th>Reason for Rejection</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if (mysqli_num_rows($rejectedResult) > 0) {
                                            while ($row = mysqli_fetch_assoc($rejectedResult)) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['post_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                                echo "<td><img src='" . htmlspecialchars($row['image_url']) . "' alt='Image' width='50' height='50'></td>";
                                                echo "<td>" . htmlspecialchars($row['place_type']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['MuniName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['reason_for_rejection']) . "</td>";
                                                echo "<td><a href='../user-post-details.php?id=" . htmlspecialchars($row['post_id']) . "' class='btn btn-sm btn-outline-primary me-1'>View</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>No rejected posts available.</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>
