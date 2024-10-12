<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Ensure the user is logged in and has the proper role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

// Function to fetch users based on the logged-in user's role and search query
function getUsers($conn, $search_query = '') {
    $search_query = trim($search_query);
    // Fetch only users with the role 'user' (excluding admins and superadmins)
    $query = "SELECT * FROM users WHERE role = 'user' AND (username LIKE ? OR Fullname LIKE ? OR Email LIKE ?)";
    $stmt = $conn->prepare($query);
    $search_term = '%' . $search_query . '%';
    $stmt->bind_param('sss', $search_term, $search_term, $search_term);
    $stmt->execute();
    return $stmt->get_result();
}

// Handle search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch users
$users = getUsers($conn, $search_query);

// Function to log admin actions for transparency
function logActivity($conn, $admin_user_id, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $admin_user_id, $_SESSION['username'], $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);

    // Fetch the role of the user to be deleted
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($user_role);
    $stmt->fetch();
    $stmt->close();

    // Restrict admins from deleting superadmins
    if ($_SESSION['role'] !== 'superadmin' && $user_role === 'superadmin') {
        echo "<script>alert('You do not have permission to delete this user.'); window.location.href = 'Manage-user.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // Log the action
    logActivity($conn, $_SESSION['user_id'], "Deleted user ID $user_id");

    header('Location: Manage-user.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Manage Users</title>
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
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Users</h5>
                            <form method="GET" action="Manage-user.php" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Search by username, fullname, or email" value="<?php echo htmlspecialchars($search_query); ?>">
                                <button type="submit" class="btn btn-success">Search</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th class="d-none d-xl-table-cell">Username</th>
                                        <th class="d-none d-xl-table-cell">Account Created</th>
                                        <th class="d-none d-md-table-cell">Fullname</th>
                                        <th class="d-none d-md-table-cell">Email</th>
                                        <th class="d-none d-md-table-cell">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($users)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                        echo "<td class='d-none d-xl-table-cell'>" . htmlspecialchars($row['username']) . "</td>";
                                        echo "<td class='d-none d-xl-table-cell'>" . htmlspecialchars($row['Date_Created']) . "</td>";
                                        echo "<td class='d-none d-md-table-cell'>" . htmlspecialchars($row['Fullname']) . "</td>";
                                        echo "<td class='d-none d-md-table-cell'>" . htmlspecialchars($row['Email']) . "</td>";
                                        echo "<td class='d-none d-md-table-cell'>";
                                        // Restrict editing/deleting based on role
                                        if ($_SESSION['role'] === 'superadmin' || ($row['role'] !== 'superadmin' && $_SESSION['role'] === 'admin')) {
                                            echo "<a href='controller/edit_user.php?id=" . htmlspecialchars($row['user_id']) . "' class='btn btn-sm btn-primary me-1'>Edit</a>";
                                            echo "<form action='Manage-user.php' method='POST' style='display:inline;'>";
                                            echo "<input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'>";
                                            echo "<button type='submit' name='delete_user' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</button>";
                                            echo "</form>";
                                        } else {
                                            echo "<span class='badge bg-secondary'>No Action Available</span>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (mysqli_num_rows($users) == 0): ?>
                            <p class="text-center mt-3">No users found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>

</html>
