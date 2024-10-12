<?php
session_start();
require 'connection/connection.php'; // Adjust the path to your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    $_SESSION['message'] = "You need to log in to view your blogs.";
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Pagination variables
$limit = 12; // Number of blogs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch the logged-in user's blogs with pagination
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch the logged-in user's blogs with pagination (excluding archived blogs)
$sql = "SELECT blog_id, title, author, approval_status, image_url, content, created_at 
        FROM blogs 
        WHERE author = ? AND archived = 0 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $username, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of blogs for pagination (excluding archived blogs)
$sql_count = "SELECT COUNT(blog_id) AS total_blogs FROM blogs WHERE author = ? AND archived = 0";
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
    <title>Your Blogs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f5f7;
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
        }

        .status-pending {
            background-color: orange;
            color: white;
        }

        .status-approved {
            background-color: green;
            color: white;
        }

        .status-rejected {
            background-color: red;
            color: white;
        }

        .pagination {
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="javascript:history.back()" class="btn btn-secondary mb-3">Back</a>

        <h2>Your Blogs</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($blog = $result->fetch_assoc()): ?>
                    <?php
                    $blog_id = htmlspecialchars($blog['blog_id']);
                    $title = htmlspecialchars($blog['title']);
                    $author = htmlspecialchars($blog['author']);
                    $status = strtolower($blog['approval_status']);
                    $image_url = htmlspecialchars($blog['image_url']);
                    $content_snippet = cleanContent($blog['content'], 100); // Clean the content and strip styles
                    ?>
                    <div class="col-md-4 blog-item mb-4">
                        <div class="card h-100 bg-white border border-light shadow-sm attraction-card">
                            <img src="assets/images/blogs/user/<?php echo $image_url; ?>" alt="<?php echo $title; ?>" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title" style="font-weight: 600; font-size: 1.5rem;"><?php echo $title; ?></h5>
                                <p class="text-uppercase text-secondary mb-2" style="font-size: 0.9rem;"><?php echo $author; ?></p>
                                <p class="text-muted" style="font-size: 1rem;"><?php echo $content_snippet; ?></p>
                                <span class="blog-status status-<?php echo $status; ?>"><?php echo ucfirst($blog['approval_status']); ?></span>
                            </div>
                            <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <a class="btn btn-outline-success" href="view-user-blogs.php?id=<?php echo $blog_id; ?>" style="font-weight: 500; font-size: 0.95rem; padding: 0.5rem 1rem; border-radius: 50px;">Read More</a>

                                <!-- 3-dot menu for Edit/Delete -->
                                <div class="dropdown">
                                    <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $blog_id; ?>" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; padding: 8px;">
                                        <i class="fas fa-ellipsis-v" style="font-size: 1.2rem; color: #555;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $blog_id; ?>">
                                        <!-- Edit Blog -->
                                        <li>
                                            <a class="dropdown-item" href="user-edit-blog.php?id=<?php echo $blog_id; ?>">
                                                <i class="fas fa-edit"></i> Edit Blog
                                            </a>
                                        </li>
                                        <!-- Delete Blog -->
                                        <li>
                                            <form action="Backend/delete_blog_user.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this blog?');">
                                                    <i class="fas fa-trash-alt"></i> Delete Blog
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p>You have not posted any blogs yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Blog Pagination">
            <ul class="pagination">
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