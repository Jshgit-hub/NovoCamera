<?php
session_start();
include '../connection/connection.php';

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../Login.php');
    exit();
}

// Handle form submission for adding a new activity
if (isset($_POST['add_activity'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $category_id = $_POST['category_id'];
    $image_url = null;

    // Check if an activity image was uploaded
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $image_name = $_FILES['image_url']['name'];
        $image_tmp_name = $_FILES['image_url']['tmp_name'];
        $upload_dir = '../assets/images/uploads/activities/';
        $image_path = $upload_dir . basename($image_name);

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            $image_url = basename($image_name); // Save only the image name
        }
    }

    $stmt = $conn->prepare("INSERT INTO activities (name, description, location, latitude, longitude, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddis", $name, $description, $location, $latitude, $longitude, $category_id, $image_url);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Added a new activity: $name");
        $_SESSION['message'] = "Activity added successfully!";
    } else {
        $_SESSION['message'] = "Failed to add activity.";
    }
    header('Location: manage-activities.php');
    exit();
}

// Handle form submission for editing an activity
if (isset($_POST['edit_activity'])) {
    $activity_id = $_POST['activity_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $category_id = $_POST['category_id'];
    $image_url = $_POST['existing_image'];

    // Check if a new activity image was uploaded
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $image_name = $_FILES['image_url']['name'];
        $image_tmp_name = $_FILES['image_url']['tmp_name'];
        $upload_dir = '../assets/images/uploads/activities/';
        $image_path = $upload_dir . basename($image_name);

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            $image_url = basename($image_name); // Save only the image name
        }
    }

    $stmt = $conn->prepare("UPDATE activities SET name = ?, description = ?, location = ?, latitude = ?, longitude = ?, category_id = ?, image_url = ? WHERE activity_id = ?");
    $stmt->bind_param("sssddisi", $name, $description, $location, $latitude, $longitude, $category_id, $image_url, $activity_id);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Edited activity: $name");
        $_SESSION['message'] = "Activity updated successfully!";
    } else {
        $_SESSION['message'] = "Failed to update activity.";
    }
    header('Location: manage-activities.php');
    exit();
}

// Handle deletion of an activity
if (isset($_POST['delete_activity'])) {
    $activity_id = intval($_POST['activity_id']);
    $query = "DELETE FROM activities WHERE activity_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $activity_id);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Deleted activity ID $activity_id successfully.");
        $_SESSION['message'] = "Activity deleted successfully!";
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['Username'], "Failed to delete activity ID $activity_id.");
        $_SESSION['message'] = "Failed to delete the activity.";
    }
    header('Location: manage-activities.php');
    exit();
}

// Set the limit of activities per page
$limit = 10;  // Number of activities per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Current page
$offset = ($page - 1) * $limit;  // Calculate the offset

// Fetch categories for the dropdown in the activities form and for listing
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Search and Filter Logic
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch activities with filtering, searching, and pagination
$activities = [];
$query = "SELECT activities.*, categories.name AS category_name 
          FROM activities 
          LEFT JOIN categories ON activities.category_id = categories.category_id 
          WHERE 1";

if ($search_term) {
    $query .= " AND (activities.name LIKE ? OR activities.location LIKE ?)";
}
if ($category_filter) {
    $query .= " AND activities.category_id = ?";
}

$query .= " ORDER BY activities.activity_id DESC LIMIT ? OFFSET ?";  // Add pagination

$stmt = $conn->prepare($query);

// Bind parameters based on search, filter, and pagination values
if ($search_term && $category_filter) {
    $search_term_like = '%' . $search_term . '%';
    $stmt->bind_param("ssiis", $search_term_like, $search_term_like, $category_filter, $limit, $offset);
} elseif ($search_term) {
    $search_term_like = '%' . $search_term . '%';
    $stmt->bind_param("ssis", $search_term_like, $search_term_like, $limit, $offset);
} elseif ($category_filter) {
    $stmt->bind_param("iis", $category_filter, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $activities = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch total number of activities for pagination
$total_query = "SELECT COUNT(*) AS total FROM activities WHERE 1";
if ($search_term) {
    $total_query .= " AND (name LIKE '%$search_term%' OR location LIKE '%$search_term%')";
}
if ($category_filter) {
    $total_query .= " AND category_id = $category_filter";
}
$total_result = $conn->query($total_query);
$total_activities = $total_result->fetch_assoc()['total'];  // Use $total_activities for total count

// Calculate total pages
$total_pages = ceil($total_activities / $limit);

$stmt->close();
?>
