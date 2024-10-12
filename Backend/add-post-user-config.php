<?php
session_start();
include '../connection/connection.php';

if (isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $userId = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['postTitle']);
    $muni_id = mysqli_real_escape_string($conn, $_POST['Muni_ID']);
    $placeType = mysqli_real_escape_string($conn, $_POST['placeType']);
    $description1 = mysqli_real_escape_string($conn, $_POST['description1']);
  

    // Handling image upload
    $file = $_FILES['postImage'];
    $filename = $file['name'];
    $temp_path = $file['tmp_name'];
    $uploadDir = '../assets/images/userUpload/';
    $imageFileName = time() . '_' . basename($filename);
    $destination = $uploadDir . $imageFileName;
    $filesize = $file['size'];

    // Validate file size and type
    $Valid = 1;

    if ($filesize > 50000000) {
        $Valid = 0;
    } else {
        $fileExtension = pathinfo($destination)['extension'];

        if (in_array($fileExtension, ['jpg','JPG', 'JPEG', 'jpeg', 'PNG', 'png',])) {
            $Valid = 1;
        } else {
            $Valid = 0;
        }
    }

    if ($Valid == 1) {
        if (move_uploaded_file($temp_path, $destination)) {
            // Mark the post as 'pending' by default
            $status = 'pending';    
            $query = "INSERT INTO post (user_id, title, image_url, Muni_ID, place_type, description1, status, created_at) 
                      VALUES ('$userId', '$title', '$destination', '$muni_id', '$placeType', '$description1', '$status', NOW())";

            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Post submitted successfully and is pending approval.'); window.location = '../userfeed.php';</script>";
            } else {
                echo "<script>alert('Database error: " . mysqli_error($conn) . "'); window.location = '../userfeed.php';</script>";
            }
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.'); window.location = '../userfeed.php';</script>";
        }
    }
}

mysqli_close($conn);

