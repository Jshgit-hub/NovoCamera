<?php
// Start session and include the connection file
session_start();
include '../connection/connection.php';

// Function to handle adding and editing services
function addOrEditService($conn, $serviceName, $serviceDescription, $serviceImage, $serviceId = null) {
    $target_dir = "../assets/images/cms/services/";
    $target_file = $target_dir . basename($serviceImage['name']);

    // Check if it's an edit operation
    if ($serviceId) {
        if (!empty($serviceImage['name'])) {
            // Update with new image
            if (move_uploaded_file($serviceImage['tmp_name'], $target_file)) {
                $sql = "UPDATE services SET service_name='$serviceName', service_description='$serviceDescription', service_image='$serviceImage[name]' WHERE service_id=$serviceId";
            }
        } else {
            // Update without new image
            $sql = "UPDATE services SET service_name='$serviceName', service_description='$serviceDescription' WHERE service_id=$serviceId";
        }
    } else {
        // Add new service
        if (move_uploaded_file($serviceImage['tmp_name'], $target_file)) {
            $sql = "INSERT INTO services (service_name, service_description, service_image) VALUES ('$serviceName', '$serviceDescription', '$serviceImage[name]')";
        }
    }

    if (mysqli_query($conn, $sql)) {
        return true;
    }
    return false;
}

// Handle form submission for add/edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceName = $_POST['service_name'];
    $serviceDescription = $_POST['service_description'];
    $serviceId = $_POST['service_id'] ?? null;  // Check if we are editing
    $serviceImage = $_FILES['service_image'];

    if (addOrEditService($conn, $serviceName, $serviceDescription, $serviceImage, $serviceId)) {
        $_SESSION['message'] = $serviceId ? 'Service updated successfully!' : 'Service added successfully!';
    } else {
        $_SESSION['message'] = 'An error occurred.';
    }
    header('Location: cms_services.php');
    exit();
}

// Handle service deletion
if (isset($_GET['delete_id'])) {
    $serviceId = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM services WHERE service_id=$serviceId");
    $_SESSION['message'] = 'Service deleted successfully!';
    header('Location: cms_services.php');
    exit();
}

// Fetch all services for listing
$services = mysqli_query($conn, "SELECT * FROM services");

// Handle edit functionality
$serviceToEdit = null;
if (isset($_GET['edit_id'])) {
    $serviceId = $_GET['edit_id'];
    $serviceToEdit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM services WHERE service_id=$serviceId"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
    <link href="css/app.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
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
                            <h5 class="card-title mb-0">Manage Services</h5>
                        </div>
                        <div class="card-body">
                            <!-- Display message -->
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                            <?php endif; ?>

                            <!-- Form for Adding/Editing Services -->
                            <form action="cms_services.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="service_id" value="<?php echo $serviceToEdit['service_id'] ?? ''; ?>">
                                <div class="mb-3">
                                    <label for="service_name" class="form-label">Service Name</label>
                                    <input type="text" class="form-control" name="service_name" value="<?php echo $serviceToEdit['service_name'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="service_description" class="form-label">Service Description</label>
                                    <textarea class="form-control" name="service_description" rows="5"><?php echo $serviceToEdit['service_description'] ?? ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="service_image" class="form-label">Service Image</label>
                                    <input type="file" class="form-control" name="service_image">
                                    <?php if (isset($serviceToEdit['service_image'])): ?>
                                        <img src="../assets/images/cms/services/<?php echo $serviceToEdit['service_image']; ?>" width="100" alt="Service Image">
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo isset($serviceToEdit) ? 'Update Service' : 'Add Service'; ?>
                                </button>
                                <!-- Cancel button for editing -->
                                <?php if (isset($serviceToEdit)): ?>
                                    <a href="cms_services.php" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </form>

                            <!-- Table Listing Services -->
                            <table class="table mt-4">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($services)): ?>
                                    <tr>
                                        <td><?php echo $row['service_name']; ?></td>
                                        <td><?php echo $row['service_description']; ?></td>
                                        <td><img src="../assets/images/cms/services/<?php echo $row['service_image']; ?>" width="50" alt=""></td>
                                        <td>
                                            <a href="cms_services.php?edit_id=<?php echo $row['service_id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="cms_services.php?delete_id=<?php echo $row['service_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this service?');">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: 'textarea',
            plugins: 'lists link image preview',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | link image',
            branding: false
        });
    </script>
</body>
</html>
