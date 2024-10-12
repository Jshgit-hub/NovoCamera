<?php
session_start();
include('../connection/connection.php');

// Logging function to record activities in the database
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Initialize variables
$place_type = '';
$icon = '';
$isEditing = false;
$edit_id = 0;

// Handle form submission for Insert or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $place_type = htmlspecialchars($_POST['place_type']);
    $icon = $_FILES['icon'];
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    if (isset($_POST['edit_id']) && $_POST['edit_id'] != 0) {
        // Update existing place type
        $edit_id = intval($_POST['edit_id']);
        $isEditing = true;

        $sql_fetch = "SELECT Icon FROM place_types WHERE id = ?";
        $stmt = $conn->prepare($sql_fetch);
        $stmt->bind_param('i', $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_place_type = $result->fetch_assoc();

        if ($current_place_type) {
            $iconNameToUse = $current_place_type['Icon'];

            // Handle file upload if a new icon is provided
            if ($icon['error'] === 0) {
                $iconName = basename($icon['name']);
                $targetDir = "../assets/Images/icon/";
                $targetFilePath = $targetDir . $iconName;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                // Allow only certain file formats
                $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowedTypes)) {
                    if (move_uploaded_file($icon['tmp_name'], $targetFilePath)) {
                        // Delete old icon if a new one is uploaded
                        if (file_exists($targetDir . $iconNameToUse)) {
                            unlink($targetDir . $iconNameToUse);
                        }
                        $iconNameToUse = $iconName;
                    } else {
                        $_SESSION['message'] = "Failed to upload the new icon.";
                        $_SESSION['message_type'] = "danger";
                        header("Location: insert_place_type.php");
                        exit();
                    }
                } else {
                    $_SESSION['message'] = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
                    $_SESSION['message_type'] = "danger";
                    header("Location: insert_place_type.php");
                    exit();
                }
            }

            $sql_update = "UPDATE place_types SET PlaceType = ?, Icon = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param('ssi', $place_type, $iconNameToUse, $edit_id);

            if ($stmt->execute()) {
                $action = "Updated place type '{$place_type}' with ID '{$edit_id}'.";
                logActivity($conn, $user_id, $username, $action);
                $_SESSION['message'] = "Place type updated successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error updating place type.";
                $_SESSION['message_type'] = "danger";
            }
        }
    } else {
        // Insert new place type
        if ($icon['error'] === 0) {
            $iconName = basename($icon['name']);
            $targetDir = "../assets/Images/icon/";
            $targetFilePath = $targetDir . $iconName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            // Allow only certain file formats
            $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($icon['tmp_name'], $targetFilePath)) {
                    // Insert into database
                    $sql = "INSERT INTO place_types (PlaceType, Icon) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ss', $place_type, $iconName);

                    if ($stmt->execute()) {
                        $action = "Inserted new place type '{$place_type}' with icon '{$iconName}'.";
                        logActivity($conn, $user_id, $username, $action);
                        $_SESSION['message'] = "Place type inserted successfully.";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Error inserting place type.";
                        $_SESSION['message_type'] = "danger";
                    }
                } else {
                    $_SESSION['message'] = "Failed to upload icon.";
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                $_SESSION['message'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Error uploading the icon.";
            $_SESSION['message_type'] = "danger";
        }
    }

    header("Location: insert_place_type.php");
    exit();
}

// Handle delete functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $sql_fetch = "SELECT Icon FROM place_types WHERE id = ?";
    $stmt = $conn->prepare($sql_fetch);
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $place_type = $result->fetch_assoc();

    if ($place_type) {
        $sql_delete = "DELETE FROM place_types WHERE id = ?";
        $stmt = $conn->prepare($sql_delete);
        $stmt->bind_param('i', $delete_id);

        if ($stmt->execute()) {
            // Delete the icon file if it exists
            $file_path = "../assets/Images/icon/" . $place_type['Icon'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $action = "Deleted place type with ID '{$delete_id}'.";
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], $action);
            $_SESSION['message'] = "Place type deleted successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting place type.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Place type not found.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: insert_place_type.php");
    exit();
}

// Fetch existing place types
$sql_fetch = "SELECT * FROM place_types";
$result_fetch = $conn->query($sql_fetch);
$place_types = [];
if ($result_fetch && $result_fetch->num_rows > 0) {
    while ($row = $result_fetch->fetch_assoc()) {
        $place_types[] = $row;
    }
}

// Fetch data to pre-fill form for editing
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $sql_fetch = "SELECT * FROM place_types WHERE id = ?";
    $stmt = $conn->prepare($sql_fetch);
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $place_type_data = $result->fetch_assoc();

    if ($place_type_data) {
        $place_type = $place_type_data['PlaceType'];
        $icon = $place_type_data['Icon'];
        $isEditing = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta and CSS links -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Edit' : 'Insert'; ?> Place Type</title>
    <link href="css/app.css" rel="stylesheet">
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
                            <h5 class="card-title mb-0 text-light"><?php echo $isEditing ? 'Edit' : 'Insert'; ?> Place Type</h5>
                        </div>
                        <div class="card-body">
                            <!-- Display session message -->
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $_SESSION['message']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['message']); ?>
                            <?php endif; ?>

                            <form action="insert_place_type.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                                <div class="mb-3">
                                    <label for="placeType" class="form-label">Place Type</label>
                                    <input type="text" class="form-control" id="placeType" name="place_type" value="<?php echo htmlspecialchars($place_type); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icon</label>
                                    <input type="file" class="form-control" id="icon" name="icon" accept="image/*" <?php echo $isEditing ? '' : 'required'; ?>>
                                    <?php if ($isEditing && $icon): ?>
                                        <div class="mt-2">
                                            <img src="../assets/Images/icon/<?php echo htmlspecialchars($icon); ?>" alt="<?php echo htmlspecialchars($place_type); ?>" width="50">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Update' : 'Insert'; ?> Place Type</button>
                                <a href="insert_place_type.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>

                    <!-- Display existing place types -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Existing Place Types</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Place Type</th>
                                        <th>Icon</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($place_types) > 0): ?>
                                        <?php foreach ($place_types as $place_type): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($place_type['PlaceType']); ?></td>
                                                <td>
                                                    <img src="../assets/Images/icon/<?php echo htmlspecialchars($place_type['Icon']); ?>" alt="<?php echo htmlspecialchars($place_type['PlaceType']); ?>" width="40" height="40">
                                                </td>
                                                <td>
                                                    <a href="insert_place_type.php?edit_id=<?php echo $place_type['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a href="insert_place_type.php?delete_id=<?php echo $place_type['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this place type?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No place types found.</td>
                                        </tr>
                                    <?php endif; ?>
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
