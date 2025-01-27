<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

// Check if the form was submitted
if (isset($_POST['submit'])) {
    $imageTitle = $_POST['ImageTitle'];
    $latitude = $conn->real_escape_string($_POST['latitude']);
    $longitude = $conn->real_escape_string($_POST['longitude']);
    $place_type = $conn->real_escape_string($_POST['place_type']);
    
    // Handle municipality for admin or superadmin
    if ($_SESSION['role'] === 'admin') {
        $user_id = $_SESSION['user_id'];
        $sql_muni_id = "SELECT Muni_ID FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql_muni_id);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $municipality = $row['Muni_ID'];
        } else {
            $_SESSION['error'] = "Failed to fetch municipality for the admin.";
            header('Location: ../admin-gallery.php');
            exit();
        }
        $stmt->close();
    } else {
        $municipality = $conn->real_escape_string($_POST['municipality']);
    }

    $imageFileName = ''; // To store the image file name

    // Handle existing selected images
    if (isset($_POST['selected_image']) && !empty($_POST['selected_image'])) {
        $selectedImage = $_POST['selected_image']; // Get the selected image identifier

        if (strpos($selectedImage, 'place-') !== false) {
            // It's a place image, so extract the Place_ID
            $place_id = str_replace('place-', '', $selectedImage);
            $sql_get_image = "SELECT PlacePicture AS ImagePath FROM places WHERE Place_ID = ?";
            $stmt = $conn->prepare($sql_get_image);
            $stmt->bind_param("i", $place_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $sourcePath = "../../assets/images/ad-places/" . basename($row['ImagePath']);
                $imageFileName = basename($row['ImagePath']); // Store only the image file name

                // Move the image to the gallery directory
                $targetDir = realpath('../../assets/images/gallery/') . '/';
                $targetFile = $targetDir . $imageFileName;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                // Debugging: Check if source file exists
                if (!file_exists($sourcePath)) {
                    $_SESSION['error'] = "Source file does not exist: " . $sourcePath;
                    header('Location: ../admin-gallery.php');
                    exit();
                }

                // Debugging: Try to copy the file and log errors
                if (!copy($sourcePath, $targetFile)) {
                    $_SESSION['error'] = "Failed to copy the image. Check file permissions. Source: $sourcePath, Target: $targetFile";
                    header('Location: ../admin-gallery.php');
                    exit();
                }
            }
            $stmt->close();

            // Insert the image details into the gallery_images table, including Place_ID
            $sql = "INSERT INTO gallery_images (title, image_path, latitude, longitude, Muni_ID, place_type, Place_ID) 
                    VALUES ('$imageTitle', '$imageFileName', '$latitude', '$longitude', '$municipality', '$place_type', '$place_id')";
        } elseif (strpos($selectedImage, 'post-') !== false) {
            // It's a post image, so extract the Post_ID and username
            preg_match('/post-(\d+)-user-(.+)/', $selectedImage, $matches);
            $post_id = $matches[1];
            $username = $matches[2];

            $sql_get_image = "SELECT image_url AS ImagePath FROM post WHERE post_id = ?";
            $stmt = $conn->prepare($sql_get_image);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $sourcePath = "../../assets/images/userUpload/" . basename($row['ImagePath']);
                $imageFileName = basename($row['ImagePath']); // Store only the image file name

                // Move the image to the gallery directory
                $targetDir = realpath('../../assets/images/gallery/') . '/';
                $targetFile = $targetDir . $imageFileName;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                // Debugging: Check if source file exists
                if (!file_exists($sourcePath)) {
                    $_SESSION['error'] = "Source file does not exist: " . $sourcePath;
                    header('Location: ../admin-gallery.php');
                    exit();
                }

                // Debugging: Try to copy the file and log errors
                if (!copy($sourcePath, $targetFile)) {
                    $_SESSION['error'] = "Failed to copy the image. Check file permissions. Source: $sourcePath, Target: $targetFile";
                    header('Location: ../admin-gallery.php');
                    exit();
                }
            }
            $stmt->close();

            // Insert the image details into the gallery_images table, including Post_ID
            $sql = "INSERT INTO gallery_images (title, image_path, latitude, longitude, Muni_ID, place_type, post_id, username) 
                    VALUES ('$imageTitle', '$imageFileName', '$latitude', '$longitude', '$municipality', '$place_type', '$post_id', '$username')";
        }

        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Image added successfully.";
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }
    } elseif (!empty($_FILES["GalleryImage"]["name"])) {
        // Handle the image upload if no existing image was selected
        $targetDir = realpath('../../assets/images/gallery/') . '/';
        $imageFileType = strtolower(pathinfo($_FILES["GalleryImage"]["name"], PATHINFO_EXTENSION));
        $imageFileName = uniqid() . '.' . $imageFileType; // Generate a unique file name
        $targetFile = $targetDir . $imageFileName;

        // Check if the uploaded file is an actual image
        $check = getimagesize($_FILES["GalleryImage"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header('Location: ../admin-gallery.php');
            exit();
        }

        // Check file size (e.g., max 5MB)
        if ($_FILES["GalleryImage"]["size"] > 5000000) {
            $_SESSION['error'] = "Sorry, your file is too large.";
            header('Location: ../admin-gallery.php');
            exit();
        }

        // Allow certain file formats
        $allowedFileTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowedFileTypes)) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header('Location: ../admin-gallery.php');
            exit();
        }

        // Try to upload the file
        if (move_uploaded_file($_FILES["GalleryImage"]["tmp_name"], $targetFile)) {
            // Insert the image details into the gallery_images table
            $sql = "INSERT INTO gallery_images (title, image_path, latitude, longitude, Muni_ID, place_type) 
                    VALUES ('$imageTitle', '$imageFileName', '$latitude', '$longitude', '$municipality', '$place_type')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success'] = "Image added successfully.";
            } else {
                $_SESSION['error'] = "Error: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
            header('Location: ../admin-gallery.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Please select an existing image or upload a new one.";
        header('Location: ../admin-gallery.php');
        exit();
    }

    // Redirect back to the gallery management page
    header('Location: ../admin-gallery.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: ../admin-gallery.php');
    exit();
}
