<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Define the upload directory (make sure this folder exists and is writable)
$uploadDir = '../assets/images/tinymce/blogs/';  // Ensure this folder exists and is writable

// Define the public URL (this is what will be returned to TinyMCE)
$publicUrl = '/assets/images/tinymce/blogs/';  // Adjust this to the correct public URL

// Check if a file is uploaded
if (isset($_FILES['file']['name'])) {
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    // Check if the file type is allowed
    if (in_array($fileExt, $allowed)) {
        // Check if there are no errors with the upload
        if ($fileError === 0) {
            // Set a unique name for the file to avoid overwriting
            $fileNewName = uniqid('', true) . "." . $fileExt;

            // Define the full path for the file (on the filesystem)
            $fileDestination = $uploadDir . $fileNewName;

            // Move the file to the upload directory
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // Construct the file URL (this is what will be returned to TinyMCE)
                $fileUrl = $publicUrl . $fileNewName;

                // Return a JSON response with the file URL (using JSON_UNESCAPED_SLASHES to avoid escaping slashes)
                echo json_encode(['location' => $fileUrl], JSON_UNESCAPED_SLASHES);
            } else {
                // Error moving the file
                http_response_code(500);
                echo json_encode(['error' => 'Failed to upload the image.']);
            }
        } else {
            // File error
            http_response_code(500);
            echo json_encode(['error' => 'Error uploading the file.']);
        }
    } else {
        // Invalid file type
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.']);
    }
} else {
    // No file uploaded
    http_response_code(400);
    echo json_encode(['error' => 'No file was uploaded.']);
}
