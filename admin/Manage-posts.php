<?php 
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Assuming user role and Muni_ID are stored in session
$role = $_SESSION['role'];
$muni_id = $_SESSION['Muni_ID'];

// Initialize search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch posts based on the user's role, including municipality name, and applying the search filter
function getPosts($conn, $role, $muni_id, $search_query) {
    $search_term = '%' . $search_query . '%';
    if ($role === 'superadmin') {
        // Superadmin sees all pending posts
        $query = "SELECT p.*, m.MuniName FROM post p 
                  JOIN Municipalities m ON p.muni_id = m.Muni_ID 
                  WHERE p.status = 'pending'
                  AND (p.title LIKE ? OR p.place_type LIKE ? OR m.MuniName LIKE ?)";
    } else {
        // Admin sees only pending posts from their municipality
        $query = "SELECT p.*, m.MuniName FROM post p 
                  JOIN Municipalities m ON p.muni_id = m.Muni_ID 
                  WHERE p.muni_id = ? AND p.status = 'pending'
                  AND (p.title LIKE ? OR p.place_type LIKE ? OR m.MuniName LIKE ?)";
    }

    $stmt = $conn->prepare($query);

    if ($role === 'superadmin') {
        $stmt->bind_param('sss', $search_term, $search_term, $search_term);
    } else {
        $stmt->bind_param('ssss', $muni_id, $search_term, $search_term, $search_term);
    }

    $stmt->execute();
    return $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Manage Posts</title>
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
                            <h5 class="card-title mb-0 text-light">Posts</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Form -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search by title, place type, or municipality">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Post ID</th>
                                            <th class="d-none d-xl-table-cell">Title</th>
                                            <th class="d-none d-md-table-cell">Place Type</th>
                                            <th class="d-none d-md-table-cell">Municipality</th>
                                            <th class="d-none d-md-table-cell">Created At</th>
                                            <th class="d-none d-md-table-cell">Status</th>
                                            <th class="d-none d-md-table-cell">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="postTableBody">
                                        <!-- The rows will be populated by AJAX -->
                                    </tbody>
                                </table>
                            </div>

                            <p id="noResults" style="display: none;">No posts found.</p>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script>
        // Function to perform AJAX request and update table based on search input
        function fetchPosts(searchQuery) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'ajax_admin/fetch_posts.php?search=' + encodeURIComponent(searchQuery), true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    const postTableBody = document.getElementById('postTableBody');
                    const noResults = document.getElementById('noResults');
                    
                    postTableBody.innerHTML = ''; // Clear the table body

                    if (response.length > 0) {
                        response.forEach(function (post) {
                            const row = `<tr>
                                <td>${post.post_id}</td>
                                <td class="d-none d-xl-table-cell">${post.title}</td>
                                <td class="d-none d-md-table-cell">${post.place_type}</td>
                                <td class="d-none d-md-table-cell">${post.MuniName}</td>
                                <td class="d-none d-md-table-cell">${post.created_at}</td>
                                <td class="d-none d-md-table-cell">${post.status}</td>
                                <td class="d-none d-md-table-cell text-center">
                                    <a href='action/view_post.php?id=${post.post_id}' class='btn btn-sm btn-outline-primary me-1'>View</a>
                                    <a href='action/approve_post.php?id=${post.post_id}' class='btn btn-sm btn-outline-success me-1'>Approve</a>
                                    <a href='action/reject_post.php?id=${post.post_id}' class='btn btn-sm btn-outline-danger'>Reject</a>
                                </td>
                            </tr>`;
                            postTableBody.insertAdjacentHTML('beforeend', row);
                        });
                        noResults.style.display = 'none';
                    } else {
                        noResults.style.display = 'block';
                    }
                }
            };
            xhr.send();
        }

        document.getElementById('searchInput').addEventListener('input', function () {
            fetchPosts(this.value);
        });

        // Initial fetch with no query to load all posts
        fetchPosts('');
    </script>
</body>
</html>
