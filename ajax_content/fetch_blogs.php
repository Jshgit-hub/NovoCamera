<?php
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Default values for search, filter, and pagination
$search_query = isset($_POST['search']) ? $_POST['search'] : '';
$filter_type = isset($_POST['filter']) ? $_POST['filter'] : 'All';
$items_per_page = 9; // Number of blogs per page
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Fetch the total number of blogs for pagination
$total_query = "SELECT COUNT(*) as total FROM blogs WHERE status = 'published'";
if (!empty($search_query)) {
    $total_query .= " AND (title LIKE '%$search_query%' OR content LIKE '%$search_query%')";
}
if ($filter_type !== 'All') {
    $total_query .= " AND blog_type = '$filter_type'";
}
$total_result = $conn->query($total_query);
$total_blogs = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_blogs / $items_per_page);

// Fetch the blog posts based on search, filter, and pagination
$query = "SELECT blogs.blog_id, blogs.title, blogs.image_url, blogs.content, blogs.created_at, blogs.blog_type, blogs.author, blogs.uploaded_by,
          (SELECT COUNT(*) FROM comments WHERE comments.blog_id = blogs.blog_id) AS comments_count
          FROM blogs
          WHERE blogs.status = 'published'";

if (!empty($search_query)) {
    $query .= " AND (blogs.title LIKE '%$search_query%' OR blogs.content LIKE '%$search_query%')";
}

if ($filter_type !== 'All') {
    $query .= " AND blogs.blog_type = '$filter_type'";
}

$query .= " ORDER BY blogs.created_at DESC LIMIT $items_per_page OFFSET $offset";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($blog = $result->fetch_assoc()) {
        $blog_id = $blog['blog_id'];
        $title = $blog['title'];
        $content = $blog['content'];

        // Remove <img> tags and other HTML tags to get plain text
        $content_without_images = preg_replace('/<img[^>]+\>/i', '', $content);  // Removes <img> tags
        $text_snippet = strip_tags($content_without_images); // Strip all other HTML tags

        // Create a shortened version of the text snippet
        $description_snippet = substr($text_snippet, 0, 100) . '...';

        // Use explode to split the image URLs and pick the first one
        $image_urls = explode(',', $blog['image_url']);
        $image_url = !empty($image_urls[0]) ? trim($image_urls[0]) : '';
        
        // Choose the correct folder based on the author (assuming 'admin' and 'user' are the two categories)
        $image_folder = $blog['uploaded_by'] === 'admin' ? 'admin' : 'user';

        // Adjust the image path based on your directory structure and the uploader
        if (!empty($image_url)) {
            $image_path = 'assets/images/blogs/' . $image_folder . '/' . $image_url;
        } else {
            $image_path = 'assets/images/default.jpg'; // Fallback to a default image if no image is provided
        }

        $author = $blog['author'];
        $comments_count = $blog['comments_count'];
?>
        <div class="col-md-4 blog-item mb-4">
            <div class="card h-100 bg-white border border-light shadow-sm attraction-card"
                style="border-radius: 15px; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($title); ?>" class="card-img-top" style="border-top-left-radius: 15px; border-top-right-radius: 15px; height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="" style="font-weight: 600; font-size: 1.5rem;"><?php echo htmlspecialchars($title); ?></h5>
                    <p class="text-uppercase text-secondary mb-2" style="font-size: 0.9rem;"><?php echo htmlspecialchars($author); ?></p>
                    <p class="text-muted" style="font-size: 1rem;">
                        <?php echo htmlspecialchars($description_snippet); ?>
                    </p>
                </div>
                <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <a class="btn btn-outline-success" href="blog-details.php?id=<?php echo $blog_id; ?>" style="font-weight: 500; font-size: 0.95rem; padding: 0.5rem 1rem; border-radius: 50px;">Read More <i class="ti-arrow-right"></i></a>
                    <span class="text-muted" style="font-size: 0.85rem;"><i class="ti-themify-favicon"></i> <?php echo $comments_count; ?> Comments</span>
                </div>
            </div>
        </div>
<?php
    }
} else {
    echo '<div class="text-center">No blogs found.</div>';
}

// Pagination controls
echo '<div class="d-flex justify-content-center mt-4">';
echo '<nav aria-label="Page navigation">';
echo '<ul class="pagination justify-content-center text-success">';
if ($page > 1) {
    echo '<li class="page-item"><a class="page-link" href="#" data-page="' . ($page - 1) . '">Previous</a></li>';
}
for ($i = 1; $i <= $total_pages; $i++) {
    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
}
if ($page < $total_pages) {
    echo '<li class="page-item"><a class="page-link" href="#" data-page="' . ($page + 1) . '">Next</a></li>';
}
echo '</ul>';
echo '</nav>';
echo '</div>';

$conn->close();
?>
