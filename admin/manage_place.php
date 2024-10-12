<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

// Initialize search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Fetch places based on user's role and search query
$places = [];
if ($_SESSION['role'] === 'superadmin') {
    // Superadmin can see all places
    if ($search_query) {
        $sql = "SELECT places.*, municipalities.MuniName FROM places 
                INNER JOIN municipalities ON places.Muni_ID = municipalities.Muni_ID 
                WHERE places.PlaceName LIKE ? OR municipalities.MuniName LIKE ? 
                ORDER BY municipalities.MuniName ASC";
        $stmt = $conn->prepare($sql);
        $search_term = '%' . $search_query . '%';
        $stmt->bind_param('ss', $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT places.*, municipalities.MuniName FROM places 
                INNER JOIN municipalities ON places.Muni_ID = municipalities.Muni_ID 
                ORDER BY municipalities.MuniName ASC";
        $result = $conn->query($sql);
    }
} else {
    // Admin can see only places in their municipality
    $admin_muni_id = $_SESSION['Muni_ID'];
    if ($search_query) {
        $sql = "SELECT places.*, municipalities.MuniName FROM places 
                INNER JOIN municipalities ON places.Muni_ID = municipalities.Muni_ID 
                WHERE places.Muni_ID = ? AND (places.PlaceName LIKE ? OR municipalities.MuniName LIKE ?) 
                ORDER BY municipalities.MuniName ASC";
        $stmt = $conn->prepare($sql);
        $search_term = '%' . $search_query . '%';
        $stmt->bind_param('iss', $admin_muni_id, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT places.*, municipalities.MuniName FROM places 
                INNER JOIN municipalities ON places.Muni_ID = municipalities.Muni_ID 
                WHERE places.Muni_ID = $admin_muni_id ORDER BY municipalities.MuniName ASC";
        $result = $conn->query($sql);
    }
}

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $places[] = $row;
    }
}

// Handle marking/unmarking a place as a top place
if (isset($_GET['toggle_top_place']) && $_SESSION['role'] === 'superadmin') {
    $place_id = intval($_GET['toggle_top_place']);
    $current_status = intval($_GET['current_status']);
    $new_status = $current_status ? 0 : 1;

    $query = "UPDATE places SET top_place = ? WHERE Place_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $new_status, $place_id);

    if ($stmt->execute()) {
        $action = $new_status ? "Marked as Top Place" : "Removed from Top Places";
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "$action for Place ID $place_id.");
        $message = "Place successfully updated.";
    } else {
        $message = "Failed to update the place.";
    }

    header('Location: manage_place.php'); // Refresh the page after the action
    exit();
}

// Handle deletion of a place
if (isset($_POST['delete_place'])) {
    $place_id = intval($_POST['place_id']);
    
    // Check if the user has permission to delete this place
    if ($_SESSION['role'] === 'superadmin' || ($_SESSION['role'] === 'admin' && $place['Muni_ID'] == $_SESSION['Muni_ID'])) {
        $query = "DELETE FROM places WHERE Place_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $place_id);
        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Deleted Place ID $place_id successfully.");
            $message = "Place deleted successfully!";
            header('Location: manage_place.php'); // Refresh the page after deletion
            exit();
        } else {
            logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Failed to delete Place ID $place_id.");
            $message = "Failed to delete the place.";
        }
    } else {
        $message = "You do not have permission to delete this place.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Places</title>
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        function confirmTopPlaceAction(placeName, currentStatus) {
            let action = currentStatus ? 'remove from Top Places' : 'mark as a Top Place';
            return confirm(`Are you sure you want to ${action} "${placeName}"?`);
        }
    </script>
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
                            <h5 class="card-title mb-0">Manage Places</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Form -->
                            <form method="GET" action="manage_place.php" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by place or municipality" value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>

                            <?php if (isset($message)): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Place Name</th>
                                            <th>Type</th>
                                            <th>Municipality</th>
                                            <th>Location</th>
                                            <?php if ($_SESSION['role'] === 'superadmin'): ?>
                                                <th>Top Place</th>
                                            <?php endif; ?>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($places as $place): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($place['PlaceName']); ?></td>
                                                <td><?php echo htmlspecialchars($place['PlaceType']); ?></td>
                                                <td><?php echo htmlspecialchars($place['MuniName']); ?></td>
                                                <td><?php echo htmlspecialchars($place['PlaceLocation']); ?></td>
                                                <?php if ($_SESSION['role'] === 'superadmin'): ?>
                                                    <td>
                                                        <a href="manage_place.php?toggle_top_place=<?php echo $place['Place_ID']; ?>&current_status=<?php echo $place['top_place']; ?>" 
                                                           onclick="return confirmTopPlaceAction('<?php echo htmlspecialchars(addslashes($place['PlaceName'])); ?>', <?php echo $place['top_place']; ?>);"
                                                           class="btn btn-sm <?php echo $place['top_place'] ? 'btn-danger' : 'btn-success'; ?>">
                                                            <?php echo $place['top_place'] ? 'Remove from Top Places' : 'Mark as Top Place'; ?>
                                                        </a>
                                                    </td>
                                                <?php endif; ?>
                                                <td>
                                                    <?php if ($_SESSION['role'] === 'superadmin' || ($place['Muni_ID'] == $_SESSION['Muni_ID'])): ?>
                                                        <a href="controller/edit_place.php?Place_ID=<?php echo $place['Place_ID']; ?>" class="btn btn-primary btn-sm px-3">Edit</a>
                                                        <form action="manage_place.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="place_id" value="<?php echo $place['Place_ID']; ?>">
                                                            <button type="submit" name="delete_place" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this place?');">Delete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (empty($places)): ?>
                                <p>No places found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>

</html>
