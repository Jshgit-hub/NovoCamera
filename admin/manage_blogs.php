<?php
session_start();
require '../connection/connection.php'; // Ensure this path is correct

// Initialize search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Fetch user-uploaded blogs
if ($search_query) {
    $sql_user = "SELECT * FROM blogs WHERE uploaded_by = 'user' AND (title LIKE ? OR author LIKE ?) ORDER BY created_at DESC";
    $stmt_user = $conn->prepare($sql_user);
    $search_term = '%' . $search_query . '%';
    $stmt_user->bind_param('ss', $search_term, $search_term);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_blogs = $result_user->fetch_all(MYSQLI_ASSOC);
} else {
    $sql_user = "SELECT * FROM blogs WHERE uploaded_by = 'user' ORDER BY created_at DESC";
    $result_user = $conn->query($sql_user);
    $user_blogs = $result_user->fetch_all(MYSQLI_ASSOC);
}

// Fetch admin-uploaded blogs
$sql_admin = "SELECT * FROM blogs WHERE uploaded_by = 'admin' ORDER BY created_at DESC";
$result_admin = $conn->query($sql_admin);
$admin_blogs = $result_admin->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Manage Blogs">
    <link href="css/app.css" rel="stylesheet">
    <title>Manage Blogs</title>
</head>

<body>
    <div class="wrapper">
        <?php include("includes/adminsidebar.php"); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <?php include('includes/navbar-top.php'); ?>
            </nav>
            <main class="content">
                <div class="container-fluid p-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Manage Blogs</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Form -->
                            <form method="GET" action="manage_blogs.php" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>

                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info">
                                    <?php
                                    echo $_SESSION['message'];
                                    unset($_SESSION['message']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <!-- Admin Blogs Section -->
                            <h4>Admin Blogs</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Views</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_blogs as $blog): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($blog['blog_id']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['views']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['created_at']); ?></td>
                                            <td>
                                                <a href="controller/edit_blog.php?blog_id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="controller/delete_blog.php?blog_id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this blog?');">Delete</a>
                                                <a href="controller/view-blog-details.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-info">View</a>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- User Blogs Section -->
                            <h4>User Blogs</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Approval Status</th>
                                        <th>Views</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_blogs as $blog): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($blog['blog_id']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                            <td>
                                                <?php
                                                if ($blog['approval_status'] === 'pending') {
                                                    echo '<span class="badge bg-warning">Pending</span>';
                                                } elseif ($blog['approval_status'] === 'approved') {
                                                    echo '<span class="badge bg-success">Approved</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($blog['views']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['created_at']); ?></td>
                                            <td>
                                                <a href="controller/edit_blog.php?blog_id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="controller/delete_blog.php?blog_id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this blog?');">Delete</a>
                                                <a href="controller/view-blog-details.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-info">View</a> 

                                                <?php if ($blog['approval_status'] === 'pending'): ?>
                                                    <a href="controller/approve_blog.php?blog_id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                                    <a href="controller/reject_blog.php?blog_id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (empty($user_blogs)): ?>
                                <p>No user blogs found.</p>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>

</html>