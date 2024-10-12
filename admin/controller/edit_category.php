<?php
session_start();
include '../../connection/connection.php';

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Ensure the user is logged in and is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../Login.php');
    exit();
}

// Fetch the category details based on the category_id passed via GET
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->bind_param('i', $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

$imageName = !empty($category['image_url']) ? basename($category['image_url']) : 'No image uploaded';

// Handle form submission for editing the category
if (isset($_POST['edit_category'])) {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];
    $category_image = $_POST['existing_image'];

    // Check if a new category image was uploaded
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

    // Update the category in the database
    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, image_url = ? WHERE category_id = ?");
    $stmt->bind_param("sssi", $category_name, $category_description, $category_image, $category_id);
    if ($stmt->execute()) {
        // Log the activity
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Edited category: $category_name");
        $_SESSION['message'] = "Category updated successfully!";
    } else {
        $_SESSION['message'] = "Failed to update category.";
    }
    header('Location: ../manage_categories.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edit Category">
    <link href="../css/app.css" rel="stylesheet">
    <title>Edit Category</title>
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea.tinymce',  // Apply TinyMCE to any textarea with the class "tinymce"
            menubar: false,
            plugins: 'lists link image code',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code',
            content_css: 'css/content.css'
        });
    </script>
</head>
<body>
    <div class="wrapper">

        <div class="main">
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Edit Category</h5>
                        </div>
                        <div class="card-body">
                            <!-- Edit Category Form -->
                            <form method="POST" action="edit_category.php?id=<?php echo $category['category_id']; ?>" enctype="multipart/form-data">
                                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($category['image_url']); ?>">
                                <div class="form-group">
                                    <label for="category_name">Category Name</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="category_description">Category Description</label>
                                    <textarea class="form-control tinymce" id="category_description" name="category_description" rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="category_image">Upload New Image</label>
                                    <input type="file" class="form-control" id="category_image" name="category_image" accept="image/*">
                                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($imageName); ?>">

                                    <div class="mt-2">
                                        <?php if (!empty($imageName) && $imageName !== 'No image uploaded'): ?>
                                            <div>
                                                <img src="../../assets/images/uploads/categories/<?php echo htmlspecialchars($imageName); ?>" alt="Current Image" style="width: 100px; height: auto; display: block; margin-bottom: 10px;">
                                                <span>Current Image: <?php echo htmlspecialchars($imageName); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div style="color: gray;">
                                                <i class="bi bi-image" style="font-size: 24px;"></i> No image uploaded.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button type="submit" name="edit_category" class="btn btn-primary mt-3">Update Category</button>
                                <a href="../manage_categories.php" class="btn btn-secondary mt-3">Cancel</a>
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
