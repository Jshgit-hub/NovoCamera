<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'novocamera');
// Get the blog ID from the URL
$blog_id = $_GET['blog_id'];

// Fetch blog details for editing
$sql_blog = "SELECT * FROM blogs WHERE blog_id = ?";
$stmt = $conn->prepare($sql_blog);
$stmt->bind_param('i', $blog_id);
$stmt->execute();
$result_blog = $stmt->get_result();
$blog = $result_blog->fetch_assoc();

// Fetch unique blog types from the blogs table
$sql_blog_type = "SELECT DISTINCT blog_type FROM blogs WHERE blog_type IS NOT NULL";
$result_blog_type = $conn->query($sql_blog_type);
$blog_types = [];
if ($result_blog_type && $result_blog_type->num_rows > 0) {
    while ($row = $result_blog_type->fetch_assoc()) {
        $blog_types[] = $row['blog_type'];
    }
}

// Fetch all municipalities with MuniName
$sql_municipalities = "SELECT MuniName FROM municipalities";
$result_municipalities = $conn->query($sql_municipalities);
$municipalities = [];
if ($result_municipalities && $result_municipalities->num_rows > 0) {
    while ($row = $result_municipalities->fetch_assoc()) {
        $municipalities[] = $row['MuniName'];
    }
}

// Handle form submission for updating blog
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);
    $author = htmlspecialchars($_POST['author']);
    $blog_type = htmlspecialchars($_POST['blog_type']);
    $municipality = htmlspecialchars($_POST['municipality']);
    $status = htmlspecialchars($_POST['status']);

    // Update the blog
    $sql_update = "UPDATE blogs SET title = ?, content = ?, author = ?, blog_type = ?, municipality_id = (SELECT Muni_ID FROM municipalities WHERE MuniName = ?), status = ? WHERE blog_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('ssssssi', $title, $content, $author, $blog_type, $municipality, $status, $blog_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Blog updated successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: manage_blogs.php");
        exit();
    } else {
        $_SESSION['message'] = "Error updating blog.";
        $_SESSION['message_type'] = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
    <link href="../css/app.css" rel="stylesheet">
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body>
    <div class="wrapper">
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
            </nav>
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Edit Blog</h5>
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

                            <form action="edit_blog.php?blog_id=<?php echo $blog_id; ?>" method="POST">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Content</label>
                                    <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($blog['author']); ?>" required>
                                </div>

                                <!-- Blog Type Dropdown -->
                                <div class="mb-3">
                                    <label for="blog_type" class="form-label">Blog Type</label>
                                    <select class="form-control" id="blog_type" name="blog_type" required>
                                        <?php foreach ($blog_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>" <?php if ($blog['blog_type'] == $type) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Municipality Dropdown -->
                                <div class="mb-3">
                                    <label for="municipality" class="form-label">Municipality</label>
                                    <select class="form-control" id="municipality" name="municipality" required>
                                        <?php foreach ($municipalities as $muni): ?>
                                            <option value="<?php echo htmlspecialchars($muni); ?>" <?php if ($blog['municipality_id'] == $muni) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($muni); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Status Dropdown -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="draft" <?php if ($blog['status'] == 'draft') echo 'selected'; ?>>Draft</option>
                                        <option value="published" <?php if ($blog['status'] == 'published') echo 'selected'; ?>>Published</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Blog</button>
                                <a href="../manage_blogs.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <!-- Initialize TinyMCE -->
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
                    xhr.withCredentials = false; // No credentials needed
                    xhr.open('POST', '../upload_image.php'); // Adjust the path to your PHP file

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
