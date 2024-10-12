<?php
session_start();
include 'connection/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Get the blog ID from the URL
if (isset($_GET['id'])) {
    $blog_id = intval($_GET['id']);
} else {
    $_SESSION['message'] = "Invalid blog ID.";
    header('Location: userfeed.php');
    exit;
}

// Fetch blog details from the database
$sql = "SELECT * FROM blogs WHERE blog_id = ? AND author = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $blog_id, $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $blog = $result->fetch_assoc();
} else {
    $_SESSION['message'] = "Blog not found or you do not have permission to edit it.";
    header('Location: userfeed.php');
    exit;
}

// Fetch municipalities
$municipalities = [];
$sql = "SELECT Muni_ID, MuniName FROM municipalities";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }
}

// Handle the form submission for editing
if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $municipality_id = $_POST['municipality'];
    $blog_type = $_POST['blog_type'];
    $image_display_type = $_POST['image_display_type'];

    // Handle image upload if a new one is uploaded
    $new_images = $_FILES['images']['name'];
    if (!empty($new_images[0])) {
        $uploaded_images = [];
        foreach ($new_images as $index => $image) {
            $target_dir = "assets/images/blogs/user/";
            $target_file = $target_dir . time() . basename($image);
            move_uploaded_file($_FILES['images']['tmp_name'][$index], $target_file);
            $uploaded_images[] = $target_file;
        }
        $image_urls = implode(',', $uploaded_images);
    } else {
        // If no new image is uploaded, use the old image URL
        $image_urls = $blog['image_url'];
    }

    // Update the blog details in the database
    $sql_update = "UPDATE blogs SET title = ?, content = ?, image_url = ?, blog_type = ?, municipality_id = ?, image_display_type = ? WHERE blog_id = ? AND author_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssisiis", $title, $content, $image_urls, $blog_type, $municipality_id, $image_display_type, $blog_id, $user_id);

    if ($stmt_update->execute()) {
        $_SESSION['message'] = "Blog post updated successfully!";
    } else {
        $_SESSION['message'] = "Failed to update the blog post.";
    }

    header('Location: user_blogs.php');
    exit();
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="blog-container" style="width: 900px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <div class="blog-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Edit Blog</h2>
                <p style="color: #666;">Update your blog post</p>
            </div>

            <form method="POST" action="Backend/edit_blog_user.php" enctype="multipart/form-data">
                <input type="hidden" name="blog_id" value="<?php echo htmlspecialchars($blog['blog_id']); ?>">

                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg bg-light fs-6" id="title" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" placeholder="Blog Title" required style="border-radius: 10px;">
                </div>

                <div class="mb-3">
                    <textarea class="form-control form-control-lg bg-light fs-6" id="content" name="content" rows="10" placeholder="Blog Content" style="border-radius: 10px;"><?php echo htmlspecialchars($blog['content']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="images" class="form-label">Update Images (Optional)</label>
                    <input type="file" class="form-control bg-light" id="images" name="images[]" multiple style="border-radius: 10px;">
                    <small class="form-text text-muted">You can upload new images. Leave empty to keep existing images.</small>
                </div>

                <div class="mb-3">
                    <label for="image_display_type" class="form-label">Image Display Type</label>
                    <select class="form-select bg-light fs-6" id="image_display_type" name="image_display_type" required style="border-radius: 10px;">
                        <option value="static" <?php echo $blog['image_display_type'] === 'static' ? 'selected' : ''; ?>>Static (Single Image)</option>
                        <option value="slider" <?php echo $blog['image_display_type'] === 'slider' ? 'selected' : ''; ?>>Slider (Multiple Images)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="blog_type" class="form-label">Blog Type</label>
                    <select class="form-select bg-light fs-6" id="blog_type" name="blog_type" required style="border-radius: 10px;">
                        <option value="historical" <?php echo $blog['blog_type'] === 'historical' ? 'selected' : ''; ?>>Historical</option>
                        <option value="events" <?php echo $blog['blog_type'] === 'events' ? 'selected' : ''; ?>>Events</option>
                        <option value="natural_attractions" <?php echo $blog['blog_type'] === 'natural_attractions' ? 'selected' : ''; ?>>Natural Attractions</option>
                        <option value="festival" <?php echo $blog['blog_type'] === 'festival' ? 'selected' : ''; ?>>Festival</option>
                        <option value="food" <?php echo $blog['blog_type'] === 'food' ? 'selected' : ''; ?>>Food</option>
                        <option value="travel_guide" <?php echo $blog['blog_type'] === 'travel_guide' ? 'selected' : ''; ?>>Travel Guide</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="municipality" class="form-label">Municipality</label>
                    <select class="form-select bg-light fs-6" id="municipality" name="municipality" required style="border-radius: 10px;">
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?php echo htmlspecialchars($municipality['Muni_ID']); ?>">
                                <?php echo htmlspecialchars($municipality['MuniName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" name="submit" class="btn btn-lg fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px; width: 48%;">Update Blog Post</button>
                    <!-- Cancel Button -->
                    <a href="user_blogs.php" class="btn btn-lg fs-6" style="background-color: #f44336; color: #fff; border: none; border-radius: 10px; padding: 10px; width: 48%;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea#content', // Adjust this to match your textarea ID
            plugins: 'image link media table code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | image media link | code',
            automatic_uploads: true,
            file_picker_types: 'image',

            images_upload_handler: function(blobInfo) {
                return new Promise(function(resolve, reject) {
                    var xhr, formData;

                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', 'admin/upload_image.php'); // Adjust the path to your PHP file

                    xhr.onload = function() {
                        if (xhr.status !== 200) {
                            reject('HTTP Error: ' + xhr.status); // Reject the Promise if there's an error
                            return;
                        }

                        var json;

                        try {
                            json = JSON.parse(xhr.responseText); // Parse the JSON response
                        } catch (err) {
                            reject('Invalid JSON: ' + xhr.responseText); // Handle invalid JSON response
                            return;
                        }

                        if (!json || typeof json.location !== 'string') {
                            reject('Invalid JSON: ' + xhr.responseText); // Reject if the location is not valid
                            return;
                        }

                        resolve(json.location); // Resolve the Promise with the image URL
                    };

                    xhr.onerror = function() {
                        reject('Image upload failed due to a network error.'); // Handle network errors
                    };

                    formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename()); // Append the file to the form data

                    xhr.send(formData); // Send the form data to the server
                });
            }
        });
    </script>
</body>

</html>