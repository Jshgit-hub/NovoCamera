<?php
session_start();
include 'connection/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$municipalities = [];
$sql = "SELECT Muni_ID, MuniName FROM municipalities";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }
}
$blog_types = [];
$sql_blog_type = "SELECT DISTINCT blog_type FROM blogs";
$result_blog_type = $conn->query($sql_blog_type);

if ($result_blog_type && $result_blog_type->num_rows > 0) {
    while ($row = $result_blog_type->fetch_assoc()) {
        $blog_types[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="blog-container" style="width: 900px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px;">
            <div class="blog-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Create a New Blog</h2>
                <p style="color: #666;">Share your experience with us</p>
            </div>

            <form method="POST" action="Backend/create-blog-user.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg bg-light fs-6" id="title" name="title" placeholder="Blog Title" required style="border-radius: 10px;">
                </div>

                <div class="mb-3">
                    <textarea class="form-control form-control-lg bg-light fs-6" id="content" name="content" rows="10" placeholder="Blog Content" style="border-radius: 10px;"></textarea>
                </div>

                <div class="mb-3">
                    <label for="images" class="form-label">Upload Images</label>
                    <input type="file" class="form-control bg-light" id="images" name="images[]" multiple style="border-radius: 10px;">
                    <small class="form-text text-muted">You can upload multiple images.</small>
                </div>

                <div class="mb-3">
                    <label for="image_display_type" class="form-label">Image Display Type</label>
                    <select class="form-select bg-light fs-6" id="image_display_type" name="image_display_type" required style="border-radius: 10px;">
                        <option value="static">Static (Single Image)</option>
                        <option value="slider">Slider (Multiple Images)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="blog_type" class="form-label">Blog Type</label>
                    <select class="form-select bg-light fs-6" id="blog_type" name="blog_type" required style="border-radius: 10px;">
                        <?php foreach ($blog_types as $blog_type): ?>
                            <option value="<?php echo htmlspecialchars($blog_type['blog_type']); ?>">
                                <?php echo htmlspecialchars($blog_type['blog_type']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="mb-3">
                    <label for="municipality" class="form-label">Municipality</label>
                    <select class="form-select bg-light fs-6" id="municipality" name="municipality" required style="border-radius: 10px;">
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?php echo $municipality['Muni_ID']; ?>">
                                <?php echo $municipality['MuniName']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" name="submit" class="btn btn-lg fs-6" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; padding: 10px; width: 48%;">Create Blog Post</button>
                    <!-- Cancel Button -->
                    <a href="userfeed.php" class="btn btn-lg fs-6" style="background-color: #f44336; color: #fff; border: none; border-radius: 10px; padding: 10px; width: 48%;">Cancel</a>
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
                    xhr.withCredentials = false; // No credentials needed
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