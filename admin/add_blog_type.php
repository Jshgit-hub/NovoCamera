<?php
session_start();
include('../connection/connection.php');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Handle form submission for adding a new blog type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_blog_type'])) {
    $blog_type = htmlspecialchars($_POST['blog_type']);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Insert new blog type into blogs table
    $sql = "INSERT INTO blogs (blog_type) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $blog_type);

    if ($stmt->execute()) {
        $action = "Added new blog type '{$blog_type}' by admin.";
        logActivity($conn, $user_id, $username, $action);
        $_SESSION['message'] = "Blog type added successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding blog type.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: add_blog_type.php");
    exit();
}

// Handle edit functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_blog_type'])) {
    $new_blog_type = htmlspecialchars($_POST['new_blog_type']);
    $old_blog_type = htmlspecialchars($_POST['old_blog_type']);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Update the blog type in the blogs table
    $sql_update = "UPDATE blogs SET blog_type = ? WHERE blog_type = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('ss', $new_blog_type, $old_blog_type);

    if ($stmt->execute()) {
        $action = "Edited blog type '{$old_blog_type}' to '{$new_blog_type}' by admin.";
        logActivity($conn, $user_id, $username, $action);
        $_SESSION['message'] = "Blog type updated successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating blog type.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: add_blog_type.php");
    exit();
}

// Handle delete functionality
if (isset($_GET['delete_id'])) {
    $delete_blog_type = htmlspecialchars($_GET['delete_id']);
    
    // Delete all blogs with the specified blog_type
    $sql_delete = "DELETE FROM blogs WHERE blog_type = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param('s', $delete_blog_type);

    if ($stmt->execute()) {
        $action = "Deleted blog type '{$delete_blog_type}' by admin.";
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], $action);
        $_SESSION['message'] = "Blog type deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting blog type.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: add_blog_type.php");
    exit();
}

// Fetch distinct blog types from the blogs table
$sql_fetch = "SELECT DISTINCT blog_type FROM blogs WHERE blog_type IS NOT NULL";
$result_fetch = $conn->query($sql_fetch);
$blog_types = [];
if ($result_fetch && $result_fetch->num_rows > 0) {
    while ($row = $result_fetch->fetch_assoc()) {
        $blog_types[] = $row['blog_type'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog Type</title>
    <link href="css/app.css" rel="stylesheet">
    <!-- Bootstrap CSS for Modal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            <h5 class="card-title mb-0">Add New Blog Type</h5>
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

                            <form action="add_blog_type.php" method="POST">
                                <div class="mb-3">
                                    <label for="blog_type" class="form-label">Blog Type</label>
                                    <input type="text" class="form-control" id="blog_type" name="blog_type" required>
                                </div>
                                <button type="submit" name="add_blog_type" class="btn btn-primary">Add Blog Type</button>
                            </form>
                        </div>
                    </div>

                    <!-- Display existing blog types -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Existing Blog Types</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Blog Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($blog_types) > 0): ?>
                                        <?php foreach ($blog_types as $blog_type): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($blog_type); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-blogtype="<?php echo htmlspecialchars($blog_type); ?>">Edit</button>
                                                    <a href="add_blog_type.php?delete_id=<?php echo urlencode($blog_type); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this blog type?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center">No blog types found.</td>
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

    <!-- Modal for Editing Blog Type -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_blog_type.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Blog Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="old_blog_type" id="old_blog_type">
                        <div class="mb-3">
                            <label for="new_blog_type" class="form-label">New Blog Type</label>
                            <input type="text" class="form-control" id="new_blog_type" name="new_blog_type" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_blog_type" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script to pass blog type to the modal -->
    <script>
        var editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var blogType = button.getAttribute('data-blogtype');
            
            var oldBlogTypeInput = editModal.querySelector('#old_blog_type');
            var newBlogTypeInput = editModal.querySelector('#new_blog_type');
            
            oldBlogTypeInput.value = blogType;
            newBlogTypeInput.value = blogType;
        });
    </script>
</body>

</html>
