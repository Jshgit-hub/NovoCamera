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

    $place_type = $conn->real_escape_string($_POST['place_type']);
    $imagePath = '';

    // Handle existing selected images
    if (isset($_POST['selected_images']) && !empty($_POST['selected_images'])) {
        $selectedImage = $_POST['selected_images'][0]; // Get the selected image file path

        if (!empty($selectedImage)) {
            // Construct the absolute source path based on the relative path
            $sourcePath = realpath('../' . $selectedImage); // Assuming the selected image is a relative path like ../assets/images/ad-places/...

            // Ensure the source file exists
            if ($sourcePath && file_exists($sourcePath)) {
                $imageFileType = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)); // Get the file extension
                $newFileName = uniqid() . '.' . $imageFileType; // Generate a unique name for the copied image
                $targetFile = realpath('../../assets/images/gallery/') . '/' . $newFileName; // Define the target directory and file name
                
                // Ensure target directory exists
                if (!is_dir(dirname($targetFile))) {
                    mkdir(dirname($targetFile), 0755, true);
                }

                // Copy the selected image to the gallery directory
                if (copy($sourcePath, $targetFile)) {
                    $imagePath = $newFileName; // Store the new file name to the database
                } else {
                    $_SESSION['error'] = "Failed to copy the selected image.";
                    header('Location: ../admin-gallery.php');
                    exit();
                }
            } else {
                $_SESSION['error'] = "Selected image does not exist or path is invalid.";
                header('Location: ../admin-gallery.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "No image selected.";
            header('Location: ../admin-gallery.php');
            exit();
        }
    } elseif (!empty($_FILES["GalleryImage"]["name"])) {
        // Handle the image upload if no existing image was selected
        $targetDir = realpath('../../assets/images/gallery/') . '/';
        $imageFileType = strtolower(pathinfo($_FILES["GalleryImage"]["name"], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $imageFileType; // Generate a unique file name
        $targetFile = $targetDir . $newFileName;

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
            $imagePath = $newFileName;
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

    // Insert the image details into the gallery_images table
    if (!empty($imagePath)) {
        // Insert image details into the database
        $sql = "INSERT INTO gallery_images (title, description, image_path, latitude, longitude, Muni_ID, place_type) 
                VALUES ('$imageTitle', '$imagePath', '$latitude', '$longitude', '$municipality', '$place_type')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Image added successfully.";
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Image path is empty. Please try again.";
    }

    // Redirect back to the gallery management page
    header('Location: ../admin-gallery.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: ../admin-gallery.php');
    exit();
}
