<?php 
session_start();
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Function to get approved posts
function getApprovedPosts($conn, $role, $muni_id) {
    if ($role === 'superadmin') {
        $query = "SELECT p.*, m.MuniName FROM post p 
                  JOIN Municipalities m ON p.muni_id = m.Muni_ID
                  WHERE p.status = 'Approved'";
    } else {
        $query = "SELECT p.*, m.MuniName FROM post p 
                  JOIN Municipalities m ON p.muni_id = m.Muni_ID 
                  WHERE p.muni_id = '$muni_id' AND p.status = 'Approved'";
    }
    return mysqli_query($conn, $query);
}

$role = $_SESSION['role'];
$muni_id = $_SESSION['Muni_ID'];
$approvedPosts = getApprovedPosts($conn, $role, $muni_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="../css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include("../includes/adminsidebar.php"); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <?php include('../includes/navbar-top.php'); ?>
            </nav>
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0 text-light">Approved Posts</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Post ID</th>
                                        <th class="d-none d-xl-table-cell">Title</th>
                                        <th class="d-none d-md-table-cell">Location</th>
                                        <th class="d-none d-md-table-cell">Place Type</th>
                                        <th class="d-none d-md-table-cell">Municipality</th>
                                        <th class="d-none d-md-table-cell">Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        while ($row = mysqli_fetch_assoc($approvedPosts)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['post_id'] . "</td>";
                                            echo "<td class='d-none d-xl-table-cell'>" . $row['title'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['location'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['place_type'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['MuniName'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['created_at'] . "</td>";
                                            echo "</tr>";
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
