<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; 
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and is an admin or superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../Login.php');
    exit();
}

// Get form data
$place_name = mysqli_real_escape_string($conn, $_POST['PlaceName']);
$place_type = mysqli_real_escape_string($conn, $_POST['PlaceType']);
$muni_id = intval($_POST['Muni_ID']);
$description = mysqli_real_escape_string($conn, $_POST['PlaceDescription']);
$location = mysqli_real_escape_string($conn, $_POST['PlaceLocation']);
$latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
$longitude = mysqli_real_escape_string($conn, $_POST['longitude']);

// Handle file upload for header image
$header_file = $_FILES['PlaceHeaderImage'];
$header_filename = $header_file['name'];
$header_temp_path = $header_file['tmp_name'];
$header_destination = "../../assets/images/ad-places/" . basename($header_filename);
$header_filesize = $header_file['size'];

$valid = true;

// Validate file type and size for header image
if ($header_filesize > 5000000) { 
    $valid = false;
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Header image upload failed: File size exceeds 5MB.');
    echo "<script>alert('Header image file size exceeds 5MB.'); window.location = '../add_place.php';</script>";
    exit();
}

$header_file_extension = strtolower(pathinfo($header_filename, PATHINFO_EXTENSION));
$valid_extensions = ['jpg', 'jpeg', 'png'];

if (!in_array($header_file_extension, $valid_extensions)) {
    $valid = false;
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Header image upload failed: Invalid file type.');
    echo "<script>alert('Invalid header image file type. Only JPG, JPEG, and PNG are allowed.'); window.location = '../add_place.php';</script>";
    exit();
}

if ($valid) {
    if (move_uploaded_file($header_temp_path, $header_destination)) {
        $stmt = $conn->prepare("INSERT INTO places (PlaceName, PlaceType, Muni_ID, PlaceDescription, PlaceLocation, Latitude, Longitude, PlacePicture) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssss", $place_name, $place_type, $muni_id, $description, $location, $latitude, $longitude, $header_destination);
        
        if ($stmt->execute()) {
            $place_id = $stmt->insert_id; 

            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Place '$place_name' added successfully with header image.");

            // Handle additional image uploads for the carousel
            foreach ($_FILES['PlacePictures']['tmp_name'] as $key => $tmp_name) {
                $carousel_filename = basename($_FILES['PlacePictures']['name'][$key]);
                $carousel_destination = "../../assets/images/ad-places/carousel/" . $carousel_filename;
                $carousel_filetype = strtolower(pathinfo($carousel_filename, PATHINFO_EXTENSION));

                if (in_array($carousel_filetype, $valid_extensions) && move_uploaded_file($tmp_name, $carousel_destination)) {
                    $sql_image = "INSERT INTO place_images (Place_ID, ImagePath) VALUES (?, ?)";
                    $stmt_image = $conn->prepare($sql_image);
                    $stmt_image->bind_param('is', $place_id, $carousel_filename);
                    $stmt_image->execute();
                } else {
                    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Carousel image upload failed for ' . $carousel_filename);
                }
            }

            echo "<script>alert('Place added successfully!'); window.location = '../add_place.php';</script>";
        } else {
            logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Database insertion failed: ' . $stmt->error);
            echo "<script>alert('Database insertion failed: " . $stmt->error . "'); window.location = '../add_place.php';</script>";
        }

        $stmt->close();
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Header image upload failed: Unable to move uploaded file.');
        echo "<script>alert('Failed to move uploaded header image. Please check file permissions.'); window.location = '../add_place.php';</script>";
    }
} else {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], 'Invalid header image file type or size.');
    echo "<script>alert('Invalid header image file type or size.'); window.location = '../add_place.php';</script>";
}

mysqli_close($conn);
?>
