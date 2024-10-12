<?php
// Start the session and include your database connection
session_start();
include '../connection/connection.php';

// Handle logo upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_logo'])) {
    // Handle file upload
    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == 0) {
        $target_dir = "../assets/images/logo/";
        $file_type = mime_content_type($_FILES['logo_image']['tmp_name']);
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];

        // Check if the uploaded file is a valid image format
        if (in_array($file_type, $allowed_types)) {
            $target_file = $target_dir . basename($_FILES["logo_image"]["name"]);

            // Move uploaded file to the correct directory
            if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $target_file)) {
                $logo_url = basename($_FILES["logo_image"]["name"]);

                // Clear previous logo and insert the new one
                mysqli_query($conn, "DELETE FROM site_logo"); // Ensure only one logo is present
                $sql = "INSERT INTO site_logo (logo_url) VALUES ('$logo_url')";
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['message'] = 'Logo uploaded successfully!';
                } else {
                    $_SESSION['error'] = 'Error updating logo: ' . mysqli_error($conn);
                }
            } else {
                $_SESSION['error'] = 'Error uploading file.';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, GIF, and SVG are allowed.';
        }
    } else {
        $_SESSION['error'] = 'No file uploaded or there was an error.';
    }

    header('Location: cms-logo.php');
    exit();
}

// Fetch the current logo
$logoResult = mysqli_query($conn, "SELECT logo_url FROM site_logo ORDER BY id DESC LIMIT 1");
$logoData = mysqli_fetch_assoc($logoResult);
$current_logo = $logoData['logo_url'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Logo Management</title>
    <link href="css/app.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Include the admin sidebar -->
        <?php include("includes/adminsidebar.php"); ?>

        <div class="main">
            <!-- Include the admin navbar -->
            <nav class="navbar navbar-expand navbar-light bg-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <?php include('includes/navbar-top.php'); ?>
            </nav>

            <!-- Main content area -->
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Manage Site Logo</h5>
                        </div>
                        <div class="card-body">
                            <!-- Display success or error message -->
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                            <?php endif; ?>

                            <!-- Logo upload form -->
                            <form action="cms-logo.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="logo_image" class="form-label">Choose new logo (JPG, PNG, GIF, SVG):</label>
                                    <input type="file" class="form-control" name="logo_image" id="logo_image" accept="image/jpeg, image/png, image/gif, image/svg+xml" required>
                                </div>
                                <button type="submit" name="upload_logo" class="btn btn-primary">Upload Logo</button>
                            </form>

                            <!-- Display current logo if it exists -->
                            <?php if ($current_logo): ?>
                                <hr>
                                <h5>Current Logo:</h5>
                                <img src="../assets/images/logo/<?php echo $current_logo; ?>" alt="Current Logo" class="img-fluid" style="max-width: 200px;">
                            <?php else: ?>
                                <hr>
                                <h5>No logo has been uploaded yet.</h5>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Optional JS -->
    <script src="js/app.js"></script>
</body>
</html>
