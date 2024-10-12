<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Ensure that the user_id is an integer
$user_id = (int) $_SESSION['user_id'];

// Fetch all notifications for the user related to both posts and blogs
$query = "
    SELECT n.*, u.username, p.title AS post_title, b.title AS blog_title 
    FROM notifications n 
    LEFT JOIN users u ON n.sender_id = u.user_id 
    LEFT JOIN post p ON n.post_id = p.post_id 
    LEFT JOIN blogs b ON n.blog_id = b.blog_id 
    WHERE n.user_id = $user_id 
    ORDER BY n.created_at DESC";

$result = mysqli_query($conn, $query);

// Debugging output
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

echo "Number of notifications: " . mysqli_num_rows($result); // Debugging output
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f5f7;
            color: #18151f;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .notification-item {
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #e0f7fa;
        }
        .notification-item.read {
            background-color: #fafafa;
        }
        .notification-item .time {
            font-size: 0.9rem;
            color: #888;
        }
        .notification-item .message {
            font-size: 1.1rem;
            color: #333;
        }
        .notification-header {
            margin-bottom: 20px;
            text-align: center;
            color: #379777;
            font-size: 1.5rem;
        }
        .mark-read-btn {
            background-color: #379777;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .mark-read-btn:hover {
            background-color: #2d5d6f;
        }
    </style>
</head>
<body>

<div class="container">
        <h2 class="notification-header">Notifications</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($notification = mysqli_fetch_assoc($result)): ?>
                <div class="notification-item <?php echo $notification['is_read'] ? 'read' : ''; ?>">
                    <p class="message">
                        <?php
                        // Display the notification message
                        echo htmlspecialchars($notification['message']);
                        // Check if the notification is for a post or a blog
                        if (!empty($notification['post_title'])) {
                            echo " for the post: <strong>" . htmlspecialchars($notification['post_title']) . "</strong>";
                        } elseif (!empty($notification['blog_title'])) {
                            echo " for the blog: <strong>" . htmlspecialchars($notification['blog_title']) . "</strong>";
                        }
                        ?>
                    </p>
                    <p class="time"><?php echo date('D, M jS Y, h:i A', strtotime($notification['created_at'])); ?></p>
                    <?php if (!$notification['is_read']): ?>
                        <form method="POST" action="mark_as_read.php">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                            <button type="submit" class="mark-read-btn">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>
</body>
</html>
