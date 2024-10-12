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

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the admin ID from the URL
$admin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission for updating admin details
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $municipality = intval($_POST['municipality']);

    $update_query = "
        UPDATE users 
        SET username = '$username', Fullname = '$fullname', Email = '$email', Muni_ID = '$municipality'
        WHERE user_id = $admin_id
    ";

    if (mysqli_query($conn, $update_query)) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Updated admin details for Admin ID $admin_id.");
        header("Location: ../Manage-admin.php?msg=updated"); // Redirect after update
        exit();
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to update admin details for Admin ID $admin_id: " . mysqli_error($conn));
        echo "Error updating record: " . mysqli_error($conn);
    }
}

// Fetch the admin details for the given ID
$query = "
    SELECT u.user_id, u.username, u.Fullname, u.Email, u.Muni_ID, m.MuniName 
    FROM users u 
    INNER JOIN Municipalities m ON u.Muni_ID = m.Muni_ID 
    WHERE u.user_id = $admin_id
";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to fetch details for Admin ID $admin_id.");
    die("Admin not found.");
}

$admin = mysqli_fetch_assoc($result);
logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Fetched details for Admin ID $admin_id.");

// Fetch municipalities for the dropdown
$municipalities = mysqli_query($conn, "SELECT Muni_ID, MuniName FROM Municipalities");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Admin Details</title>
    <link href="css/app.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white text-center">
                        <h4>Update Admin Details</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-4">
                                <label for="username" class="form-label">username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($admin['Fullname']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['Email']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="municipality" class="form-label">Municipality</label>
                                <select class="form-select" id="municipality" name="municipality" required>
                                    <?php while ($row = mysqli_fetch_assoc($municipalities)) { ?>
                                        <option value="<?php echo $row['Muni_ID']; ?>" <?php if ($admin['Muni_ID'] == $row['Muni_ID']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($row['MuniName']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-success me-2">Save Changes</button>
                                <a href="admin_list.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
