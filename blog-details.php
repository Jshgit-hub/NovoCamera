<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');
ob_start(); // Start output bufferin
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['id'])) {
  $blog_id = intval($_GET['id']);

  // Increment view count for the blog
  $sql = "UPDATE blogs SET views = views + 1 WHERE blog_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $blog_id);
  $stmt->execute();

  // Fetch blog details
  $sql = "SELECT * FROM blogs WHERE blog_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $blog_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $blog = $result->fetch_assoc();

  if (!$blog) {
    echo "Blog not found!";
    exit;
  }

  // Count the number of comments for this blog
  $comment_count_sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE blog_id = ?";
  $comment_count_stmt = $conn->prepare($comment_count_sql);
  $comment_count_stmt->bind_param('i', $blog_id);
  $comment_count_stmt->execute();
  $comment_count_result = $comment_count_stmt->get_result();
  $comment_count_data = $comment_count_result->fetch_assoc();
  $comments_count = $comment_count_data['comment_count'];

  // Handle new comment submission
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session
    $comment = $_POST['comment'];

    $comment_insert_sql = "INSERT INTO comments (blog_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    $comment_stmt = $conn->prepare($comment_insert_sql);
    $comment_stmt->bind_param('iis', $blog_id, $user_id, $comment);
    $comment_stmt->execute();
    header("Location: blog-details.php?id=$blog_id"); // Refresh the page to display the new comment
    exit();
  }
} else {
  echo "No blog ID provided!";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="icon" href="img/Fevicon.png" type="image/png">
  <link rel="stylesheet" href="vendors/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="vendors/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <link rel="stylesheet" href="css/style-2.css">
  <title><?php echo htmlspecialchars($blog['title']); ?> - Nueva Ecija Tourism</title>
  <style>
    /* Banner Styling */
    .banner-area {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.7)), url('assets/images/banner/banner-blogs/tradition.jpg') no-repeat center;
      background-size: cover;
      height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      position: relative;
    }

    .banner-area h2 {
      font-size: 36px;
      font-weight: bold;
      text-transform: uppercase;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    }

    .breadcrumb {
      background: rgba(255, 255, 255, 0.2);
      padding: 10px 20px;
      border-radius: 5px;
    }

    .breadcrumb-item a {
      color: white;
      text-decoration: none;
    }

    .breadcrumb-item.active {
      color: #ffd700;
      /* Golden color for the current page */
    }

    /* Main Content Styling */
    .main_blog_details {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .main_blog_details img {
      max-height: 450px;
      object-fit: cover;
      width: 100%;
      margin-bottom: 20px;
      border-radius: 10px;
    }

    .main_blog_details h4 {
      font-size: 28px;
      color: #333;
      margin-bottom: 20px;
    }

    .main_blog_details p {
      line-height: 1.8;
      font-size: 16px;
      color: #666;
    }

    .main_blog_details .news_d_footer {
      border-top: 1px solid #eee;
      padding-top: 20px;
      margin-top: 20px;
    }

    .news_socail a {
      font-size: 18px;
      margin-right: 15px;
      color: #666;
      transition: color 0.3s ease;
    }

    .news_socail a:hover {
      color: #379777;
    }

    /* Comments Section */
    .single-comment .thumb img {
      border-radius: 50%;
      width: 60px;
      height: 60px;
      border: 3px solid #379777;
    }

    .single-comment .desc {
      background: #f1f1f1;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .single-comment .desc:before {
      content: '';
      position: absolute;
      top: 10px;
      left: -15px;
      border-width: 15px;
      border-style: solid;
      border-color: transparent #f1f1f1 transparent transparent;
    }

    .comment-form .submit_btn {
      background: linear-gradient(45deg, #379777, #2d6a5f);
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 5px;
      transition: background 0.3s ease;
      font-size: 16px;
      font-weight: bold;
    }

    .comment-form .submit_btn:hover {
      background: linear-gradient(45deg, #2d6a5f, #379777);
    }

    /* Map Styling */
    #map {
      height: 400px;
      border: 2px solid #ddd;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }

    /* Sidebar Styling */
    .single-sidebar-widget__title {
      font-size: 20px;
      color: #333;
      margin-bottom: 20px;
      border-bottom: 2px solid #379777;
      padding-bottom: 10px;
    }

    .popular-post-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .popular-post-list .post-item {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .popular-post-list .post-item img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 5px;
    }

    .popular-post-list .post-item h6 {
      font-size: 16px;
      color: #333;
      margin: 0;
    }
  </style>
</head>

<body>
  <!--================ Start Banner Area =================-->
  <section class="banner-area">
    <div class="content">
      <h2 class="text-light">Blog Details</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="Blogs.php">Blogs</a></li>
          <li class="breadcrumb-item active text-success" aria-current="page">Blog Details</li>
        </ol>
      </nav>
    </div>
  </section>
  <!--================ End Banner Area =================-->

  <!--================ Start Blog Post Area =================-->

  <section class="blog-post-area section-margin">
    <div class="container">
      <div class="row">
        <div class="col-lg-8">
          <div class="main_blog_details">
            <?php
            // Determine the image folder based on the uploader (assuming the author indicates who uploaded the blog)
            $image_folder = $blog['uploaded_by'] === 'admin' ? 'admin' : 'user';

            $images = explode(',', $blog['image_url']); // Split the image URLs by comma
            if ($blog['image_display_type'] == 'slider' && count($images) > 1): ?>
              <div id="imageSlider" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <?php foreach ($images as $index => $image): ?>
                    <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                      <img src="../../assets/images/blogs/<?php echo $image_folder; ?>/<?php echo htmlspecialchars($image); ?>" class="d-block w-100" alt="Blog Image">
                    </div>
                  <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#imageSlider" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#imageSlider" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Next</span>
                </button>
              </div>
            <?php else: ?>
              <img class="img-fluid" src="../../assets/images/blogs/<?php echo $image_folder; ?>/<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
            <?php endif; ?>
            <a href="#">
              <h4><?php echo htmlspecialchars($blog['title']); ?></h4>
            </a>
            <div class="user_details">
              <div class="float-left">
                <a href="#"><?php echo htmlspecialchars($blog['blog_type']); ?></a>
              </div>
              <div class="float-right mt-sm-0 mt-3">
                <div class="media">
                  <div class="media-body">
                    <h5 class="text-dark"><?php echo htmlspecialchars($blog['author']); ?></h5>
                    <p class="text-muted"><?php echo date('F j, Y', strtotime($blog['created_at'])); ?></p>
                  </div>
                  <div class="d-flex">
                    <img width="42" height="42" src="img/blog/user-img.png" alt="">
                  </div>
                </div>
              </div>
            </div>
            <p><?php echo nl2br(($blog['content'])); ?></p>

            <!-- Social Media and Comments Section -->
            <div class="news_d_footer flex-column flex-sm-row">
              <div class="news_socail ml-sm-auto mt-sm-0 mt-2">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-dribbble"></i></a>
                <a href="#"><i class="fab fa-behance"></i></a>
              </div>
            </div>
          </div>

          <!-- Comments Section -->
          <div class="comments-area">
            <h4><?php echo $comments_count; ?> Comments</h4>
            <!-- Fetch and Display Comments -->
            <?php
            $comment_sql = "SELECT comments.*, users.username FROM comments INNER JOIN users ON comments.user_id = users.user_id WHERE comments.blog_id = ? ORDER BY comments.created_at DESC";
            $comment_stmt = $conn->prepare($comment_sql);
            $comment_stmt->bind_param('i', $blog_id);
            $comment_stmt->execute();
            $comments = $comment_stmt->get_result();

            while ($comment = $comments->fetch_assoc()):
            ?>
              <div class="comment-list">
                <div class="single-comment justify-content-between d-flex">
                  <div class="user justify-content-between d-flex">
                    <div class="thumb">
                      <img src="img/blog/user-img.png" alt="">
                    </div>
                    <div class="desc">
                      <h5><a href="#"><?php echo htmlspecialchars($comment['username']); ?></a></h5>
                      <p class="date"><?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?></p>
                      <p class="comment text-muted">
                        <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                      </p>
                    </div>
                  </div>
                  <div class="reply-btn">
                    <a href="" class="btn-reply text-uppercase">Reply</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>

          <!-- Leave a Reply Form -->
          <div class="comment-form">
            <h4>Leave a Reply</h4>
            <form method="POST" action="">
              <div class="form-group">
                <textarea class="form-control mb-10" rows="5" name="comment" placeholder="Message" required=""></textarea>
              </div>
              <button type="submit" class="button submit_btn">Post Comment</button>
            </form>
          </div>
        </div>

        <!-- Start Blog Post Sidebar -->
        <div class="col-lg-4 sidebar-widgets">
          <div class="widget-wrap">
            <div class="single-sidebar-widget popular-post-widget bg-light">
              <h4 class="single-sidebar-widget__title">More Like This</h4>
              <div class="popular-post-list" id="related-blogs">
                <!-- Related blogs will be loaded here via AJAX -->
                <?php include 'fetch_relatedblogs.php'; ?>
              </div>
              <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                  <ul class="pagination">
                    <!-- Pagination buttons will be dynamically inserted here -->
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
  </section>

  <!--================ End Blog Post Area =================-->

  <?php include 'includes/footer.php' ?>

  <script src="vendors/jquery/jquery-3.2.1.min.js"></script>
  <script src="vendors/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>


  <script>
    $(document).ready(function() {
      loadRelatedBlogs();

      function loadRelatedBlogs() {
        $.ajax({
          url: 'ajax_content/fetch_related_blogs.php',
          type: 'GET',
          data: {
            blog_id: <?php echo $blog_id; ?>,
            blog_type: '<?php echo $blog['blog_type']; ?>'
          },
          success: function(response) {
            $('#related-blogs').html(response);
          },
          error: function(xhr, status, error) {
            console.error(error);
          }
        });
      }

    });
  </script>
</body>

</html>