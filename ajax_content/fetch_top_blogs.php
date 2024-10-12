<?php
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch top blogs, either marked as top or with the highest views
$query = "SELECT blog_id, title, image_url, author, content, created_at 
          FROM blogs 
          WHERE is_top_blog = 1 OR views > 100 
          ORDER BY is_top_blog DESC, views DESC, created_at DESC 
          LIMIT 4";  // Limit to 4 top blogs
$result = $conn->query($query);

$blogs = [];

if ($result->num_rows > 0) {
    while ($blog = $result->fetch_assoc()) {
        $blogs[] = $blog;
    }
} else {
    echo '<div class="text-center">No top blogs found.</div>';
}

$conn->close();

if (!empty($blogs)) {
    // Split the blogs into main story and secondary stories
    $main_story = $blogs[0];
    $secondary_stories = array_slice($blogs, 1);
?>
<section class="latest-stories py-5" style="background-color: #f9f9f9;">
    <div class="container">
        <div class="row">
            <!-- Main Story -->
            <div class="col-lg-7 animate fadeInLeft">
                <a href="blog-details.php?id=<?php echo $main_story['blog_id']; ?>" class="text-decoration-none text-white">
                    <div class="main-story position-relative">
                        <?php
                        // Extract the main story image
                        $main_story_image = explode(',', $main_story['image_url'])[0];
                        ?>
                        <img src="../../assets/images/blogs/admin/<?php echo htmlspecialchars($main_story_image); ?>" alt="Main story image"
                            class="img-fluid rounded w-100 main-story-img">
                        <div class="story-content p-4 position-absolute bottom-0 start-0 bg-dark bg-opacity-50 text-white w-100">
                            <span class="story-category text-uppercase fw-bold text-warning" style="font-size: 12px;">Top Blog</span>
                            <h2 class="story-title mt-2 fw-bold text-light" style="font-size: 24px;"><?php echo htmlspecialchars($main_story['title']); ?></h2>
                            <p class="story-meta" style="font-size: 14px; color: #ddd;"><?php echo date('M d, Y', strtotime($main_story['created_at'])); ?> • 6 min read</p>
                            <p style="color: white;"><?php echo (substr($main_story['content'], 0, 100)) . '...'; ?></p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Secondary Stories -->
            <div class="col-lg-5">
                <div class="secondary-stories">
                    <?php foreach ($secondary_stories as $story): 
                        // Extract the secondary story image
                        $story_image = explode(',', $story['image_url'])[0];
                    ?>
                    <a href="blog-details.php?id=<?php echo $story['blog_id']; ?>" class="text-decoration-none text-dark animate fadeInUp">
                        <div class="story-item d-flex mb-4 story-item-hover">
                            <img src="../../assets/images/blogs/admin/<?php echo htmlspecialchars($story_image); ?>" alt="Secondary story image"
                                class="img-fluid rounded me-3" style="width: 90px; height: 90px; object-fit: cover;">
                            <div class="story-info flex-grow-1">
                                <span class="story-category text-uppercase fw-bold text-warning" style="font-size: 12px;">Popular</span>
                                <h5 class="story-title mt-1 fw-bold text-dark" style="font-size: 18px;"><?php echo htmlspecialchars($story['title']); ?></h5>
                                <p class="story-meta" style="font-size: 14px; color: #999;"><?php echo date('M d, Y', strtotime($story['created_at'])); ?> </p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CSS Styling -->
<style>
    /* Animations */
    .fadeInLeft {
        opacity: 0;
        transform: translateX(-30px);
        animation: fadeInLeft 0.8s forwards ease-in-out;
    }

    .fadeInUp {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s forwards ease-in-out;
    }

    @keyframes fadeInLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .story-item-hover:hover {
        background-color: #f1f1f1;
        transition: background-color 0.3s ease;
    }

    .main-story-img {
        transition: transform 0.5s ease;
    }

    .main-story:hover .main-story-img {
        transform: scale(1.05);
    }

    /* Typography Improvements */
    .story-title {
        font-size: 1.75rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .story-meta {
        font-size: 0.875rem;
        color: #999;
    }

    .story-category {
        font-size: 0.85rem;
        font-weight: bold;
    }

    .story-content p {
        font-size: 0.95rem;
        color: #ddd;
    }

    .secondary-stories .story-title {
        font-size: 1.25rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .story-title {
            font-size: 1.5rem;
        }

        .main-story-img {
            height: auto;
        }
    }
</style>

<?php
}
?>
