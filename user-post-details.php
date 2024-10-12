<?php
session_start();
include 'connection/connection.php';

// Get the post ID from the URL
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$user_id = $_SESSION['user_id']; // Assuming the user ID is stored in the session

$place_type_query = "SELECT DISTINCT place_type FROM post"; // Assuming 'post' table contains distinct place types
$place_type_result = $conn->query($place_type_query);

// Fetch the post data along with the municipalities name
$query = "SELECT post.user_id, post.title, post.image_url, post.description1, post.created_at, post.place_type, municipalities.MuniName
          FROM post
          INNER JOIN municipalities ON post.Muni_ID = municipalities.Muni_ID
          WHERE post.post_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
} else {
    echo "Post not found.";
    exit;
}

include 'Backend/update-users-post.php';


// Check if the logged-in user is the owner of the post
$isOwner = ((int)$post['user_id'] === (int)$user_id);

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
            color: #2d2d2d;
            background-color: #f3f5f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0px 8px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        header h1 {
            font-size: 3rem;
            color: #379777;
            margin: 0;
            font-weight: 700;
        }

        header time {
            font-size: 1rem;
            color: #7b7b7b;
            margin-top: 10px;
            display: block;
        }

        .content img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 15px;
            transition: transform 0.3s ease;
            box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.1);
        }

        .content img:hover {
            transform: scale(1.05);
        }

        .content p {
            line-height: 1.8;
            font-size: 1.2rem;
            color: #444;
            margin-bottom: 25px;
            text-align: justify;
        }

        footer {
            margin-top: 30px;
            border-top: 1px solid #ececec;
            padding-top: 20px;
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
            border-radius: 30px;
            padding: 10px 25px;
            margin: 0 10px;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        footer ul li i {
            margin-right: 8px;
        }

        .dropdown {
            position: absolute;
            top: 10px;
            right: 10px;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 150px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
            border-radius: 10px;
            text-align: left;
        }

        .dropdown-content a {
            color: #444;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #ececec;
            transition: background-color 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropbtn {
            background-color: #2d5d6f;
        }

        .dropbtn {
            background-color: #379777;
            color: white;
            padding: 12px 16px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Edit Post Form */
        #editPostForm {
            display: none;
            margin-top: 30px;
            padding: 25px;
            background-color: #f9f9f9;
            border-radius: 15px;
            box-shadow: 0px 8px 30px rgba(0, 0, 0, 0.1);
        }

        #editPostForm h2 {
            color: #379777;
            margin-bottom: 25px;
            font-size: 1.8rem;
            font-weight: 600;
        }

        #editPostForm label {
            font-weight: bold;
            color: #379777;
            display: block;
            /* Ensures label and input are on separate lines */
            margin-bottom: 8px;
        }

        #editPostForm input,
        #editPostForm textarea,
        #editPostForm select {
            width: 100%;
            /* Ensures full width alignment for inputs and dropdowns */
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 1rem;
            background-color: #ffffff;
            box-sizing: border-box;
            /* Ensures padding doesn't affect overall width */
        }

        #editPostForm button {
            background-color: #379777;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        #editPostForm button:hover {
            background-color: #2d5d6f;
        }

        .cancel-btn {
            background-color: #f44336;
            margin-left: 10px;
        }

        #editPostForm select {
            height: 45px;
            /* Set height to match other input fields */
            font-size: 1rem;
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

            <!-- Dropdown Menu for Edit/Delete (Only for the owner) -->
            <?php if ($isOwner): ?>
                <div class="dropdown">
                    <button class="dropbtn">Options</button>
                    <div class="dropdown-content">
                        <a href="#editPost" onclick="document.getElementById('editPostForm').style.display='block'">Edit Post</a>
                        <a href="#" onclick="if(confirm('Are you sure you want to delete this post?')) { document.getElementById('deletePostForm').submit(); }">Delete Post</a>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <div class="content">
            <img src="<?php echo ($post['image_url']); ?>" alt="Image Post">
            <p><?php echo nl2br(($post['description1'])); ?></p>
        </div>

        <footer>
            <ul>

                <li><i class="fas fa-map-marker-alt"></i> <?php echo ($post['MuniName']); ?></li>
            </ul>
        </footer>

        <?php
        // Fetch the list of municipalities
        $muni_query = "SELECT Muni_ID, MuniName FROM municipalities";
        $muni_result = $conn->query($muni_query);

        ?>

        <!-- Edit Post Form (Only for the owner) -->
        <?php if ($isOwner): ?>
            <div id="editPostForm">
                <h2>Edit Post</h2>
                <form method="POST" enctype="multipart/form-data">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo ($post['title']); ?>" required>

                    <label for="description1">Description Part 1</label>
                    <textarea id="description1" name="description1" required><?php echo ($post['description1']); ?></textarea>

                    <label for="place_type">Place Type</label>
                    <select id="place_type" name="place_type" required>
                        <option value="">-- Select Place Type --</option>
                        <?php while ($type = $place_type_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($type['place_type']); ?>"
                                <?php echo ($type['place_type'] == $post['place_type']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['place_type']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="location">Location (Optional)</label>
                    <select id="location" name="location">
                        <option value="">-- Select Location --</option>
                        <?php while ($muni = $muni_result->fetch_assoc()): ?>
                            <option value="<?php echo $muni['Muni_ID']; ?>"
                                <?php echo ($muni['MuniName'] == $post['MuniName']) ? 'selected' : ''; ?>>
                                <?php echo $muni['MuniName']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="postImage">Replace Image (Optional)</label>
                    <input type="file" id="postImage" name="postImage" accept="image/*">

                    <button type="submit" name="update_post">Update Post</button>
                    <button type="button" class="cancel-btn" onclick="document.getElementById('editPostForm').style.display='none'">Cancel</button>
                </form>
            </div>
            <!-- Delete Post Form -->
            <form id="deletePostForm" action="Backend/delete-post-user.php" method="POST">
                <input type="hidden" name="delete_post_id" value="<?php echo $post_id; ?>"> <!-- Pass the correct post ID -->
            </form>
 
        <?php endif; ?>
    </div>

</body>

</html>