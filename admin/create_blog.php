<?php
session_start();
include '../connection/connection.php';

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$municipalities = [];
$sql = "SELECT Muni_ID, MuniName FROM municipalities";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch/dist/geosearch.css" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }

        .suggestions-box {
            border: 1px solid #ccc;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            position: absolute;
            width: 100%;
            z-index: 1000;
        }

        .suggestions-box div {
            padding: 8px;
            cursor: pointer;
        }

        .suggestions-box div:hover {
            background-color: #f0f0f0;
        }
    </style>
    <title>Create Blog Post</title>

    <!-- TinyMCE Initialization -->
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
                    xhr.open('POST', 'upload_image.php'); // Adjust the path to your PHP file

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
                            <h5 class="card-title mb-0 text-light">Create a New Blog Post</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="controller/create_blog.php" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content</label>
                                    <!-- TinyMCE text editor for content -->
                                    <textarea class="form-control" id="content" name="content" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control" id="author" name="author" required>
                                </div>
                                <div class="mb-3">
                                    <label for="images" class="form-label">Images</label>
                                    <input type="file" class="form-control" id="images" name="images[]" multiple>
                                    <small class="form-text text-muted">
                                        If you want to upload multiple images and display them as a slider, select "Slider" below. If you want to display only a single image, select "Static".
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label for="image_display_type" class="form-label">Image Display Type</label>
                                    <select class="form-control" id="image_display_type" name="image_display_type" required>
                                        <option value="static">Static (Single Image)</option>
                                        <option value="slider">Slider (Multiple Images)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="blog_type" class="form-label">Blog Type</label>
                                    <select class="form-control" id="blog_type" name="blog_type" required>
                                        <option value="historical">Historical</option>
                                        <option value="events">Events</option>
                                        <option value="natural_attractions">Natural Attractions</option>
                                        <option value="festival">Festival</option>
                                        <option value="food">Food</option>
                                        <option value="travel_guide">Travel Guide</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="municipality" class="form-label">Municipality</label>
                                    <select class="form-control" id="municipality" name="municipality" required>
                                        <?php foreach ($municipalities as $municipality): ?>
                                            <option value="<?php echo $municipality['Muni_ID']; ?>">
                                                <?php echo $municipality['MuniName']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">Create Blog Post</button>
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