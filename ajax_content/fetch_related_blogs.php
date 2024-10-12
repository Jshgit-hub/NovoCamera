<?php
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Get the current active blog ID and its blog_type from the URL parameters or elsewhere
$active_blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;  // Get the current blog ID
$blog_type = isset($_GET['blog_type']) ? $_GET['blog_type'] : '';  // Get the current blog type

// Check if blog_type is provided
if (!$blog_type) {
    echo '<div class="text-center text-dark">No related blogs found due to missing blog type.</div>';
    exit;
}

// Prepare and execute the query to fetch related blogs with the same blog_type but excluding the active blog
$query = "SELECT blogs.blog_id, blogs.title, blogs.image_url, blogs.created_at, blogs.uploaded_by,
                 (SELECT COUNT(*) FROM comments WHERE comments.blog_id = blogs.blog_id) AS comments_count
          FROM blogs
          WHERE blogs.status = 'published'
          AND blogs.blog_id != ?  -- Exclude the active blog
          AND blogs.blog_type = ?  -- Filter by blog_type
          ORDER BY blogs.created_at DESC LIMIT 8";  // Limit to 8 results, newest first

// Prepare the statement
$stmt = $conn->prepare($query);
$stmt->bind_param('is', $active_blog_id, $blog_type);  // 'i' for integer (id), 's' for string (blog_type)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($blog = $result->fetch_assoc()) {
        $blog_id = $blog['blog_id'];
        $title = $blog['title'];
        
        // Use explode to split the image URLs and pick the first one
        $image_urls = explode(',', $blog['image_url']);
        $image_url = !empty($image_urls[0]) ? $image_urls[0] : 'assets/images/default.jpg'; // Default image if none provided

        // Determine the image folder based on the uploader (admin or user)
        $image_folder = $blog['uploaded_by'] === 'admin' ? 'admin' : 'user';

        $created_at = date('F j, Y', strtotime($blog['created_at']));
?>
        <div class="single-post-list mb-4 shadow-sm p-3 bg-white rounded">
            <div class="thumb">
                <img class="img-fluid rounded" src="../assets/images/blogs/<?php echo $image_folder; ?>/<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                <ul class="thumb-info list-unstyled mt-2">
                    <li><a href="#" class="text-muted"><?php echo htmlspecialchars($created_at); ?></a></li>
                </ul>
            </div>
            <div class="details mt-3">
                <a href="blog-details.php?id=<?php echo $blog_id; ?>" class="text-dark">
                    <h6 class="fw-bold"><?php echo htmlspecialchars($title); ?></h6>
                </a>
            </div>
        </div>
<?php
    }
} else {
    echo '<div class="text-center text-dark">No related blogs found.</div>';
}

$stmt->close();
$conn->close();
?>
