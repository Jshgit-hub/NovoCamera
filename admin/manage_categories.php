<?php
session_start();
include '../connection/connection.php';

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action)
{
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../Login.php');
    exit();
}

// Handle form submission for adding a new category
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];
    $category_image = null;

    // Check if a category image was uploaded
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == UPLOAD_ERR_OK) {
        $image_name = $_FILES['category_image']['name'];
        $image_tmp_name = $_FILES['category_image']['tmp_name'];
        $upload_dir = '../assets/images/uploads/categories/';
        $image_path = $upload_dir . basename($image_name);

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            $category_image = basename($image_name); // Save only the image name
        }
    }

    $stmt = $conn->prepare("INSERT INTO categories (name, description, image_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $category_name, $category_description, $category_image);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Added a new category: $category_name");
        $_SESSION['message'] = "Category added successfully!";
    } else {
        $_SESSION['message'] = "Failed to add category.";
    }
    header('Location: manage_categories.php');
    exit();
}

// Handle deletion of a category
if (isset($_POST['delete_category'])) {
    $category_id = intval($_POST['category_id']);
    $query = "DELETE FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $category_id);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Deleted category ID $category_id successfully.");
        $_SESSION['message'] = "Category deleted successfully!";
    } else {
        $_SESSION['message'] = "Failed to delete category.";
    }
    header('Location: manage_categories.php');
    exit();
}

// Handle search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Fetch categories for listing based on the search query
$categories = [];
$sql = "SELECT * FROM categories WHERE name LIKE ? OR description LIKE ?";
$stmt = $conn->prepare($sql);
$search_term = '%' . $search_query . '%';
$stmt->bind_param('ss', $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage Categories">
    <link href="css/app.css" rel="stylesheet">
    <title>Manage Categories</title>
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea.tinymce', // Apply TinyMCE to any textarea with the class "tinymce"
            menubar: false,
            plugins: 'lists link image code',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code',
            content_css: 'css/content.css'
        });
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
                            <h5 class="card-title mb-0">Manage Categories</h5>
                        </div>
                        <div class="card-body">
                            <!-- Add Category Form -->
                            <form method="POST" action="manage_categories.php" class="mb-3" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_name">Category Name</label>
                                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="category_description">Category Description</label>
                                            <textarea class="form-control tinymce" id="category_description" name="category_description" rows="4"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="category_image">Category Image (Optional)</label>
                                            <input type="file" class="form-control" id="category_image" name="category_image" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="add_category" class="btn btn-primary mt-3">Add Category</button>
                            </form>

                            <!-- Search Form -->
                            <form method="GET" action="manage_categories.php" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                            <!-- Existing Categories List -->
                            <h5 class="mt-4 mb-3">Existing Categories</h5>
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info">
                                    <?php
                                    echo $_SESSION['message'];
                                    unset($_SESSION['message']);
                                    ?>
                                </div>
                            <?php endif; ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>   
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo($category['name']); ?></td>
                                            <td><?php echo ($category['description']); ?></td>
                                            <td>
                                                <?php if ($category['image_url']): ?>
                                                    <img src="../assets/images/uploads/categories/<?php echo htmlspecialchars($category['image_url']); ?>" alt="Category Image" style="width: 50px; height: auto;">
                                                <?php else: ?>
                                                    No image
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="controller/edit_category.php?id=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <form method="POST" action="manage_categories.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                    <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>


                            <?php if (empty($categories)): ?>
                                <p>No categories found.</p>
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