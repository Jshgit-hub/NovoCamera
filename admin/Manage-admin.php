<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the search query if it exists
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Modify the SQL query to include the search filter
$query = "
    SELECT u.user_id, u.username, u.Fullname, u.Email, u.Date_Created, m.MuniName 
    FROM users u 
    INNER JOIN Municipalities m ON u.Muni_ID = m.Muni_ID 
    WHERE u.role = 'admin' 
    AND (u.username LIKE '%$search%' 
    OR u.Fullname LIKE '%$search%' 
    OR u.Email LIKE '%$search%' 
    OR m.MuniName LIKE '%$search%')
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
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
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-<?php echo $_GET['msg'] == 'updated' || $_GET['msg'] == 'deleted' ? 'success' : 'danger'; ?> alert-dismissible fade show shadow-sm mt-4" role="alert">
                        <div class="d-flex align-items-center">
                            <div>
                                <?php
                                if ($_GET['msg'] == 'updated') {
                                    echo "<strong>Success!</strong> Admin details updated successfully.";
                                } elseif ($_GET['msg'] == 'deleted') {
                                    echo "<strong>Success!</strong> Admin deleted successfully.";
                                } else {
                                    echo "<strong>Error!</strong> An error occurred. Please try again.";
                                }
                                ?>
                            </div>
                            <div class="ms-auto">
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="container-fluid p-0">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Admin List</h5>
                            <form method="GET" action="" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Search by username, fullname, email, or municipality" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Account Created</th>
                                        <th>Fullname</th>
                                        <th>Email</th>
                                        <th>Municipality</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Date_Created']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Fullname']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['MuniName']) . "</td>";
                                            echo "<td><a href='controller/edit_admin.php?id=" . htmlspecialchars($row['user_id']) . "' class='btn btn-sm btn-primary'>Edit</a> ";
                                            echo "<a href='controller/delete_admin.php?id=" . htmlspecialchars($row['user_id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this admin?\");'>Delete</a></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>No admin users found.</td></tr>";
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