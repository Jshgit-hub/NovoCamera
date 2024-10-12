<?php
include("../connection/connection.php");

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['MuniName']);
    $description = mysqli_real_escape_string($conn, $_POST['MuniDesc']);
    $population = filter_var($_POST['Population'], FILTER_SANITIZE_NUMBER_INT);
    $area = filter_var($_POST['Area'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $established = mysqli_real_escape_string($conn, $_POST['Established']);
    $mayor = mysqli_real_escape_string($conn, $_POST['Mayor']);
    $location = mysqli_real_escape_string($conn, $_POST['Location']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    
    // Handling file upload
    $file = $_FILES['MuniPicture'];
    $filename = $file['name'];
    $temp_path = $file['tmp_name'];
    $filesize = $file['size'];
    
    // Generate a unique filename to avoid overwriting
    $filename = time() . "_" . basename($filename);
    $destination = "../assets/images/Municipalities/" . $filename;

    // Validate file size and type
    $allowedFileTypes = ['jpg', 'JPG', 'jpeg','JPEG', 'png', 'PNG', 'gif'];
    $fileExtension = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
    $isValidFileType = in_array($fileExtension, $allowedFileTypes);
    $isValidFileSize = $filesize <= 5000000; // 5MB

    if ($isValidFileType && $isValidFileSize) {
        // Move the uploaded file to the destination folder
        if (move_uploaded_file($temp_path, $destination)) {
            $query = "INSERT INTO municipalities 
                      (MuniName, MuniDesc, MuniPicture, Population, Area, Established, Mayor, Location, Latitude, Longitude) 
                      VALUES ('$name', '$description', '$destination', '$population', '$area', '$established', '$mayor', '$location', '$latitude', '$longitude')";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                echo "<script>alert('Municipality added successfully!'); window.location = '../admin/add-municipalities.php';</script>";
            } else {
                echo "<script>alert('Database insertion failed: " . mysqli_error($conn) . "'); window.location = '../admin/add-municipalities.php';</script>";
            }
        } else {
            echo "<script>alert('Error moving the uploaded file.'); window.location = '../admin/add-municipalities.php';</script>";
        }
    } else {
        $error = !$isValidFileType ? 'Invalid file type.' : 'File size exceeds 5MB.';
        echo "<script>alert('$error'); window.location = '../admin/add-municipalities.php';</script>";
    }
}

mysqli_close($conn);
?>
