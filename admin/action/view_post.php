<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Logging function to record activities in the logs table
function logActivity($conn, $user_id, $username, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Fetch the post details based on the post_id
    $query = "SELECT p.*, m.MuniName FROM post p 
              JOIN Municipalities m ON p.muni_id = m.Muni_ID 
              WHERE p.post_id = '$post_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $post = mysqli_fetch_assoc($result);
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Fetched details for Post ID $post_id.");
    } else {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "Failed to fetch details for Post ID $post_id.");
        echo "Post not found.";
        exit();
    }
} else {
    logActivity($conn, $_SESSION['user_id'], $_SESSION['username'], "No post ID provided in request.");
    echo "No post ID provided.";
    exit();
}

// Adjust the image path based on your directory structure
$imagePath = "../../" . $post['image_url']; // Adjust this based on your actual folder structure
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($post['title']); ?></title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #18151f;
            background-color: #f3f5f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h1 {
            font-size: 2.5rem;
            color: #379777;
            margin: 0;
        }

        header time {
            font-size: 1rem;
            color: #424242;
        }

        .content img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .content img:hover {
            transform: scale(1.05);
        }

        .content p {
            line-height: 1.6;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 20px;
        }

        footer {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        footer ul {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        footer ul li {
            background: #379777;
            border-radius: 20px;
            padding: 10px 20px;
            margin: 0 10px;
            color: #fff;
            font-size: 0.9rem;
        }

        footer ul li i {
            margin-right: 8px;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #379777;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #2d5d6f;
        }

    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1><?php echo ($post['title']); ?></h1>
            <time datetime="<?php echo date('c', strtotime($post['created_at'])); ?>">
                <i class="fas fa-calendar-alt"></i> <?php echo date('D, M jS Y', strtotime($post['created_at'])); ?>
            </time>
        </header>

        <div class="content">
            <img src="<?php echo ($imagePath); ?>" alt="Image Post">
            <p><?php echo nl2br(($post['description1'])); ?></p>
        </div>

        <footer>
            <ul>
                <li><i class="fas fa-tag"></i> <?php echo ($post['place_type']); ?></li>
                <li><i class="fas fa-map-marker-alt"></i> <?php echo ($post['MuniName']); ?></li>
                <li><i class="fas fa-map-marked-alt"></i> <?php echo ($post['MuniName']); ?></li> <!-- Displaying the Municipality Name -->
            </ul>
        </footer>

        <!-- Back to Posts Button -->
        <p><a href="../Manage-posts.php" class="back-button">Back to Posts</a></p>
    </div>

</body>
</html>
