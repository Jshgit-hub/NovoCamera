<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'novocamera');


// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

// Fetch image details from the database
if (isset($_GET['id'])) {
    $imageId = intval($_GET['id']);
    $sql = "SELECT * FROM gallery_images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $image = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Image not found.";
        header('Location: manage_gallery.php');
        exit();
    }
}

// Handle form submission for editing
if (isset($_POST['submit'])) {
    $newTitle = $_POST['ImageTitle'];

    // Update the image details in the database
    $sqlUpdate = "UPDATE gallery_images SET title = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("si", $newTitle,  $imageId);
    $stmtUpdate->execute();

    $_SESSION['success'] = "Image details updated successfully.";
    header('Location: ../manage_gallery.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gallery Image</title>
    <link href="../css/app.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->


        <div class="main">
            <!-- Navbar -->
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <?php include('../includes/navbar-top.php'); ?>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Edit Image</h5>
                        </div>
                        <div class="card-body">
                            <!-- Form for editing the image details -->
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="ImageTitle" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="ImageTitle" name="ImageTitle" value="<?php echo htmlspecialchars($image['title']); ?>" required>
                                </div>
                                <div class="mb-3">
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>

</html>
