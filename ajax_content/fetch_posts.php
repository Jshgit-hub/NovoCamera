<?php
include '../connection/connection.php';

$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
$limit = 5; // Number of posts to load per request

$query = "SELECT post.post_id, post.title, post.image_url, post.description1, post.created_at, 
                 users.username, users.profile_picture,
                 (SELECT COUNT(*) FROM likes WHERE likes.post_id = post.post_id) AS likes_count,
                 (SELECT COUNT(*) FROM shares WHERE shares.post_id = post.post_id) AS shares_count
          FROM post
          INNER JOIN users ON post.user_id = users.user_id
          WHERE post.status = 'approved'
          ORDER BY post.created_at DESC
          LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $post_id = $data['post_id'];
        $title = $data['title'];
        $description_snippet = substr($data['description1'], 0, 100) . '...';
        $likes_count = $data['likes_count'];
        $shares_count = $data['shares_count'];
        $profile_picture = !empty($data['profile_picture']) ? $data['profile_picture'] : '../assets/images/profile-placeholder.png'; // Fallback to a placeholder if no profile picture
        $post_image = $data['image_url'];
        $created_at = date('F j, Y, g:i a', strtotime($data['created_at']));
?>
        <a href="user-post-details.php?post_id=<?php echo $post_id; ?>" class="text-decoration-none text-dark">
            <div class="post-container card mb-4"
                style="border-radius: 12px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); overflow: hidden; cursor: pointer;"
                data-post-id="<?php echo $post_id; ?>">

                <!-- Post Header -->
                <div class="post-header d-flex align-items-center p-3" style="background-color: #f9fafb;">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="User Avatar"
                        class="rounded-circle me-3" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid #379777;">
                    <div>
                        <h6 class="m-0" style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($data['username']); ?></h6>
                        <small class="text-muted">Posted on <?php echo $created_at; ?></small>
                    </div>
                </div>

                <!-- Post Body -->
                <div class="post-body p-0" style="transition: all 0.3s ease;">
                    <?php if (!empty($post_image)): ?>
                        <div style="width: 100%; height: 350px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($post_image); ?>" class="img-fluid" alt="Post Image"
                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                        </div>
                    <?php endif; ?>
                    <div class="p-3">
                        <h5 class="post-title" style="font-size: 1.25rem; color: #333; margin-bottom: 0.5rem; font-weight: 700;">
                            <?php echo htmlspecialchars($title); ?>
                        </h5>
                        <p class="post-content text-muted" style="font-size: 1rem; color: #666;"><?php echo ($description_snippet); ?></p>
                    </div>
                </div>

                <!-- Post Actions -->
                <div class="post-actions d-flex justify-content-between align-items-center p-3"
                    style="background-color: #f9fafb; border-top: 1px solid #eee;">
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-success btn-sm me-3" style="border-radius: 50px;"
                            onclick="event.preventDefault(); likePost(event, <?php echo $post_id; ?>, this)">
                            <i class="fas fa-thumbs-up"></i> Like <span class="like-count"><?php echo $likes_count; ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" style="border-radius: 50px;"
                            onclick="event.preventDefault(); sharePost('<?php echo urlencode($data['title']); ?>', '<?php echo urlencode($data['image_url']); ?>', '<?php echo urlencode("http://yourwebsite.com/user-post-details.php?post_id={$post_id}"); ?>');">
                            <i class="fas fa-share"></i> Share
                        </button>
                    </div>
                    <small class="text-muted"><?php echo $shares_count; ?> Shares</small>
                </div>
            </div>
        </a>    

<?php
    }
} else {
    echo '<div class="no-more-posts text-center" style="display: none;">No more posts to load.</div>';
}

$stmt->close();
$conn->close();
?>
