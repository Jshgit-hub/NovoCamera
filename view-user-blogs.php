<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style-2.css">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Blog Details</title>
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
    </style>
</head>

<body>
    <div class="container mt-3">
        <a href="javascript:history.back()" class="btn btn-primary mb-3">Back</a>
    </div>

    <!--================ Start Blog Post Area =================-->
    <section class="blog-post-area section-margin">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="main_blog_details">
                        <?php
                        $images = explode(',', $blog['image_url']); // Split the image URLs by comma
                        $image_folder = $blog['uploaded_by'] === 'admin' ? 'admin' : 'user'; // Choose the folder based on who uploaded

                        if ($blog['image_display_type'] == 'slider' && count($images) > 1): ?>
                            <div id="imageSlider" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php foreach ($images as $index => $image): ?>
                                        <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                            <img src="assets/images/blogs/user/<?php echo htmlspecialchars($image); ?>" class="d-block w-100" alt="Blog Image">
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
                            <img class="img-fluid" src="assets/images/blogs/user/<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
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
                                        <img width="42" height="42" src="img/blog/user-img.png" alt="User Image">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p><?php echo nl2br(($blog['content'])); ?></p>

                        <!-- Social Media and Footer Section -->
                        <div class="news_d_footer flex-column flex-sm-row">
                            <div class="news_socail ml-sm-auto mt-sm-0 mt-2">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-dribbble"></i></a>
                                <a href="#"><i class="fab fa-behance"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--================ End Blog Post Area =================-->

    <script src="vendors/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendors/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>
