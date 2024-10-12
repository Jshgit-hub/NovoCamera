<?php 
session_start();
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Check if the user is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../Login.php');
    exit();
}

// Fetch logs from the database
function getLogs($conn) {
    $query = "SELECT * FROM logs ORDER BY timestamp DESC"; // Fetch logs ordered by the latest activity
    return mysqli_query($conn, $query);
}

mysqli_close($conn);
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
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 text-light">Activity Logs</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Log ID</th>
                                        <th class="d-none d-xl-table-cell">User ID</th>
                                        <th class="d-none d-md-table-cell">Username</th>
                                        <th class="d-none d-md-table-cell">Action</th>
                                        <th class="d-none d-md-table-cell">IP Address</th>
                                        <th class="d-none d-md-table-cell">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $logs = getLogs($conn);
                                        while ($row = mysqli_fetch_assoc($logs)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id'] . "</td>"; 
                                            echo "<td class='d-none d-xl-table-cell'>" . $row['user_id'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['username'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['action'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['ip_address'] . "</td>";
                                            echo "<td class='d-none d-md-table-cell'>" . $row['timestamp'] . "</td>";
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
