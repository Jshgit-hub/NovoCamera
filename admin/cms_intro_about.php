<?php
// Start session and include the connection file
session_start();
include '../connection/connection.php';

// Function to handle adding or updating content
function addOrEditContent($conn, $table, $data, $id = null) {
    if ($id) {
        // Update query
        $sql = "UPDATE $table SET " . join(", ", array_map(function($key, $value) {
            return "$key = '$value'";
        }, array_keys($data), $data)) . " WHERE id = $id";
    } else {
        // Insert query
        $sql = "INSERT INTO $table (" . join(", ", array_keys($data)) . ") VALUES ('" . join("', '", $data) . "')";
    }

    return mysqli_query($conn, $sql);
}

// Handle form submission for introduction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_intro'])) {
    $introData = [
        'title1' => isset($_POST['title1']) ? $_POST['title1'] : '',  // Check if title1 exists
        'title2' => isset($_POST['title2']) ? $_POST['title2'] : '',  // Check if title2 exists
        'description' => isset($_POST['intro_description']) ? $_POST['intro_description'] : '',  // Check if intro_description exists
        'media_type' => isset($_POST['media_type']) ? $_POST['media_type'] : null,  // Check if media_type exists
        'image_url' => null,  // Default to null if no image is uploaded
        'video_file' => null  // Default to null if no video is uploaded
    ];

    // Check if media type is image and the image file is uploaded
    if ($introData['media_type'] === 'image' && isset($_FILES['intro_image']['name']) && !empty($_FILES['intro_image']['name'])) {
        move_uploaded_file($_FILES['intro_image']['tmp_name'], "../assets/images/intro/" . $_FILES['intro_image']['name']);
        $introData['image_url'] = $_FILES['intro_image']['name'];  // Set the uploaded image file name
    }

    // Check if media type is video and the video file is uploaded
    if ($introData['media_type'] === 'video' && isset($_FILES['intro_video']['name']) && !empty($_FILES['intro_video']['name'])) {
        move_uploaded_file($_FILES['intro_video']['tmp_name'], "../assets/video/intro/" . $_FILES['intro_video']['name']);
        $introData['video_file'] = $_FILES['intro_video']['name'];  // Set the uploaded video file name
    }

    // Handle adding or editing based on whether ID exists
    if (!empty($_POST['intro_id'])) {
        addOrEditContent($conn, 'introduction', $introData, $_POST['intro_id']);
        $_SESSION['message'] = 'Introduction updated successfully!';
    } else {
        addOrEditContent($conn, 'introduction', $introData);
        $_SESSION['message'] = 'Introduction added successfully!';
    }

    header('Location: cms_intro_about.php');
    exit();
}

// Handle form submission for about us
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_about_us'])) {
    $aboutUsData = [
        'title' => isset($_POST['about_title']) ? $_POST['about_title'] : '',  // Check if about_title exists
        'journey_heading' => isset($_POST['journey_heading']) ? $_POST['journey_heading'] : '',  // Check if journey_heading exists
        'description' => isset($_POST['about_description']) ? $_POST['about_description'] : '',  // Check if about_description exists
        'additional_info' => isset($_POST['additional_info']) ? $_POST['additional_info'] : '',  // Check if additional_info exists
        'image_url' => null  // Default to null if no image is uploaded
    ];

    // Check if image is provided and uploaded
    if (isset($_FILES['about_image']['name']) && !empty($_FILES['about_image']['name'])) {
        move_uploaded_file($_FILES['about_image']['tmp_name'], "../assets/images/about/" . $_FILES['about_image']['name']);
        $aboutUsData['image_url'] = $_FILES['about_image']['name'];  // Set the uploaded image file name
    }

    // Handle adding or editing based on whether ID exists
    if (!empty($_POST['about_id'])) {
        addOrEditContent($conn, 'about_us', $aboutUsData, $_POST['about_id']);
        $_SESSION['message'] = 'About Us updated successfully!';
    } else {
        addOrEditContent($conn, 'about_us', $aboutUsData);
        $_SESSION['message'] = 'About Us added successfully!';
    }

    header('Location: cms_intro_about.php');
    exit();
}

// Handle delete action for introduction
if (isset($_GET['delete_intro_id'])) {
    mysqli_query($conn, "DELETE FROM introduction WHERE id=" . $_GET['delete_intro_id']);
    $_SESSION['message'] = 'Introduction deleted successfully!';
    header('Location: cms_intro_about.php');
    exit();
}

// Handle delete action for about us
if (isset($_GET['delete_about_id'])) {
    mysqli_query($conn, "DELETE FROM about_us WHERE id=" . $_GET['delete_about_id']);
    $_SESSION['message'] = 'About Us deleted successfully!';
    header('Location: cms_intro_about.php');
    exit();
}

// Fetch existing data for Introduction and About Us
$intro = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM introduction WHERE id = 1")) ?? [];
$about_us = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us WHERE id = 1")) ?? [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Introduction & About Us</title>
    <link href="css/app.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include("includes/adminsidebar.php"); ?>

        <div class="main">
            <nav class="navbar navbar-expand navbar-light bg-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <?php include('includes/navbar-top.php'); ?>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Manage Introduction & About Us</h5>
                        </div>
                        <div class="card-body">
                            <!-- Display message -->
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                            <?php endif; ?>

                            <!-- Form for Updating Introduction Section -->
                            <form action="cms_intro_about.php" method="POST" enctype="multipart/form-data">
                                <h3>Introduction Section</h3>
                                <input type="hidden" name="intro_id" value="<?php echo $intro['id'] ?? ''; ?>">
                                <div class="mb-3">
                                    <label for="title1" class="form-label">Title 1</label>
                                    <input type="text" class="form-control" name="title1" value="<?php echo $intro['title1'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="title2" class="form-label">Title 2</label>
                                    <input type="text" class="form-control" name="title2" value="<?php echo $intro['title2'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="intro_description" class="form-label">Description</label>
                                    <textarea class="form-control" name="intro_description" rows="4" required><?php echo $intro['description'] ?? ''; ?></textarea>
                                </div>

                                <!-- Choose between image or video -->
                                <div class="mb-3">
                                    <label for="media_type" class="form-label">Choose Media Type</label>
                                    <select class="form-control" name="media_type" required>
                                        <option value="image" <?php echo ($intro['media_type'] === 'image') ? 'selected' : ''; ?>>Image</option>
                                        <option value="video" <?php echo ($intro['media_type'] === 'video') ? 'selected' : ''; ?>>Video</option>
                                    </select>
                                </div>

                                <!-- Image Upload -->
                                <div class="mb-3" id="image_upload" style="display: <?php echo ($intro['media_type'] === 'image' || empty($intro['media_type'])) ? 'block' : 'none'; ?>">
                                    <label for="intro_image" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" name="intro_image">
                                    <?php if (!empty($intro['image_url'])): ?>
                                        <img src="../assets/images/intro/<?php echo $intro['image_url']; ?>" width="100" alt="Introduction Image">
                                    <?php endif; ?>
                                </div>

                                <!-- Video Upload -->
                                <div class="mb-3" id="video_upload" style="display: <?php echo ($intro['media_type'] === 'video') ? 'block' : 'none'; ?>">
                                    <label for="intro_video" class="form-label">Upload Video</label>
                                    <input type="file" class="form-control" name="intro_video">
                                    <?php if (!empty($intro['video_file'])): ?>
                                        <video width="100" controls>
                                            <source src="../assets/videos/intro/<?php echo $intro['video_file']; ?>" type="video/mp4">
                                        </video>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" name="submit_intro" class="btn btn-primary">Save Introduction</button>
                                <?php if (isset($intro['id'])): ?>
                                    <a href="cms_intro_about.php?delete_intro_id=<?php echo $intro['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this introduction?');">Delete Introduction</a>
                                <?php endif; ?>
                            </form>

                            <hr>

                            <!-- Form for Updating About Us Section -->
                            <form action="cms_intro_about.php" method="POST" enctype="multipart/form-data">
                                <h3>About Us Section</h3>
                                <input type="hidden" name="about_id" value="<?php echo $about_us['id'] ?? ''; ?>">
                                <div class="mb-3">
                                    <label for="about_title" class="form-label">Title</label>
                                    <input type="text" class="form-control" name="about_title" value="<?php echo $about_us['title'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="journey_heading" class="form-label">Journey Heading</label>
                                    <input type="text" class="form-control" name="journey_heading" value="<?php echo $about_us['journey_heading'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="about_description" class="form-label">Description</label>
                                    <textarea class="form-control" name="about_description" rows="4" required><?php echo $about_us['description'] ?? ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="additional_info" class="form-label">Additional Info</label>
                                    <textarea class="form-control" name="additional_info" rows="4" required><?php echo $about_us['additional_info'] ?? ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="about_image" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" name="about_image">
                                    <?php if (!empty($about_us['image_url'])): ?>
                                        <img src="../assets/images/about/<?php echo $about_us['image_url']; ?>" width="100" alt="About Us Image">
                                    <?php endif; ?>
                                </div>

                                <button type="submit" name="submit_about_us" class="btn btn-primary">Save About Us</button>
                                <?php if (isset($about_us['id'])): ?>
                                    <a href="cms_intro_about.php?delete_about_id=<?php echo $about_us['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this about us section?');">Delete About Us</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
        // Toggle visibility of image and video upload fields based on media type selection
        document.querySelector('select[name="media_type"]').addEventListener('change', function() {
            const mediaType = this.value;
            if (mediaType === 'image') {
                document.getElementById('image_upload').style.display = 'block';
                document.getElementById('video_upload').style.display = 'none';
            } else if (mediaType === 'video') {
                document.getElementById('image_upload').style.display = 'none';
                document.getElementById('video_upload').style.display = 'block';
            }
        });
    </script>
</body>
</html>
