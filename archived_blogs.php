<?php
session_start();
require 'connection/connection.php'; // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    $_SESSION['message'] = "You need to log in to view your archived blogs.";
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Pagination variables
$limit = 12; // Number of blogs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch the logged-in user's archived blogs
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$sql = "SELECT blog_id, title, author, image_url, content, created_at 
        FROM blogs 
        WHERE author = ? AND archived = 1 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $username, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of archived blogs for pagination
$sql_count = "SELECT COUNT(blog_id) AS total_blogs FROM blogs WHERE author = ? AND archived = 1";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("s", $username);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_blogs = $count_result->fetch_assoc()['total_blogs'];
$total_pages = ceil($total_blogs / $limit);

// Function to clean and strip out unnecessary HTML tags and inline styles
function cleanContent($content, $length = 100)
{
    $cleaned_content = strip_tags($content, '<p><br>');
    $cleaned_content = preg_replace('/style=("|\')(.*?)("|\')/', '', $cleaned_content); // Remove inline styles
    if (mb_strlen($cleaned_content) > $length) {
        return mb_substr($cleaned_content, 0, $length) . '... <span style="color:blue;">See More</span>';
    }
    return $cleaned_content;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Blogs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            height: 200px;
            object-fit: cover;
        }

        .blog-status {
            font-weight: bold;
            text-transform: capitalize;
            font-size: 0.9rem;
            padding: 5px;
            border-radius: 5px;
            background-color: #f0ad4e;
            color: white;
        }

        .restore-btn,
        .delete-btn {
            width: 48%;
        }

        .empty-archive-btn {
            background-color: #f44336;
            color: white;
            font-size: 1rem;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .warning-text {
            color: #ff6b6b;
            font-size: 1rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-row">
            <a href="javascript:history.back()" class="btn btn-secondary mb-3">Back</a>

            <!-- Empty Archive Button -->
            <form action="Backend/empty_archive.php" method="POST">
                <button type="submit" class="btn empty-archive-btn" onclick="return confirm('Are you sure you want to delete all archived blogs?');">
                    Empty Archive
                </button>
            </form>
        </div>

        <!-- Warning Message -->
        <div class="warning-text text-center mb-4">
            Blogs that are archived will be permanently deleted after 30 days.
        </div>

        <!-- Alert for system messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Archived Blogs Grid -->
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($blog = $result->fetch_assoc()): ?>
                    <?php
                    $blog_id = htmlspecialchars($blog['blog_id']);
                    $title = htmlspecialchars($blog['title']);
                    $image_url = htmlspecialchars($blog['image_url']);
                    $content_snippet = cleanContent($blog['content'], 100);
                    ?>
                    <div class="col-md-4 blog-item mb-4">
                        <div class="card h-100 bg-white border border-light shadow-sm">
                            <img src="assets/images/blogs/user/<?php echo $image_url; ?>" alt="<?php echo $title; ?>" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title" style="font-weight: 600;"><?php echo $title; ?></h5>
                                <p class="text-muted" style="font-size: 0.95rem;"><?php echo $content_snippet; ?></p>
                                <span class="blog-status">Archived</span>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <div class="d-grid gap-2">
                                    <!-- Restore Button -->
                                    <form action="Backend/restore_blog.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
                                        <button type="submit" class="btn btn-success btn-block mb-2">Restore</button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form action="Backend/delete_forever.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
                                        <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to permanently delete this blog?');">Delete</button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted text-center">Your archive is currently empty.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Blog Pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>