    <?php session_start();

    include 'connection/connection.php';

    $introQuery = "SELECT * FROM introduction WHERE id = 1"; // Assuming you are fetching the first record
    $introResult = mysqli_query($conn, $introQuery);
    $introData = mysqli_fetch_assoc($introResult);

    // Fetch about us data
    $aboutUsQuery = "SELECT * FROM about_us WHERE id = 1"; // Assuming you are fetching the first record
    $aboutUsResult = mysqli_query($conn, $aboutUsQuery);
    $aboutUsData = mysqli_fetch_assoc($aboutUsResult);


    // Check if the user is logged in
    $isLoggedIn = isset($_SESSION['username']); // Adjusted to 'username' based on your session variable in the navbar
    $sql = "SELECT category_id, name, description, image_url FROM categories";
    $result = $conn->query($sql);

    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    $conn->close();
    ?>



    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/style-2.css">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <style>
            /* Blur effect when modal is open */
            body.modal-open .blur-background {
                filter: blur(5px);
                transition: filter 0.3s ease-in-out;
            }

            /* General transitions for smooth appearance */
            .card,
            .img-fluid,
            .card-title,
            .lead,
            .h2,
            .h3,
            .btn {
                transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.8s ease;
            }

            /* Hover effect for cards with cinematic feel */
            .card:hover,
            .main-blog-card:hover {
                transform: scale(1.1) rotate(0.5deg);
                box-shadow: 0 6px 30px rgba(0, 0, 0, 0.3);
            }

            /* Hover effect for buttons */
            .btn:hover {
                transform: translateY(-6px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            }

            /* Image hover with cinematic zoom */
            .img-fluid:hover {
                transform: scale(1.15);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            }

            /* Cinematic fade-in effect for section items */
            .col-md-5,
            .col-md-4,
            .col-lg-3 {
                opacity: 0;
                transform: scale(0.9) translateY(50px);
                transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* Add the class when in view */
            .col-md-5.in-view,
            .col-md-4.in-view,
            .col-lg-3.in-view {
                opacity: 1;
                transform: scale(1) translateY(0);
            }

            /* Staggered animations for cinematic feel */
            #top-places .col-md-4.in-view:nth-child(1) {
                transition-delay: 0.1s;
            }

            #top-places .col-md-4.in-view:nth-child(2) {
                transition-delay: 0.2s;
            }

            #top-places .col-md-4.in-view:nth-child(3) {
                transition-delay: 0.3s;
            }

            #top-places .col-md-4.in-view:nth-child(4) {
                transition-delay: 0.4s;
            }

            /* Similar staggered effect for other sections */
            #services .col-lg-3.in-view:nth-child(1) {
                transition-delay: 0.1s;
            }

            #services .col-lg-3.in-view:nth-child(2) {
                transition-delay: 0.2s;
            }

            #services .col-lg-3.in-view:nth-child(3) {
                transition-delay: 0.3s;
            }

            #services .col-lg-3.in-view:nth-child(4) {
                transition-delay: 0.4s;
            }

            /* For text sections, add cinematic fade */
            h2,
            h3 {
                opacity: 0;
                transform: translateY(20px);
                transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
            }

            h2.in-view,
            h3.in-view,
            p.in-view {
                opacity: 1;
                transform: translateY(0);
            }
        </style>
    </head>

    <body data-bs-spy="scroll" data-bs-target=".navbar" data-bs-offset="70" class="blur-background">
        <!-- Navbar -->
        <header class="header_area">
            <div class="main_menu">
                <?php include 'includes/navbar.php'; ?>
            </div>
        </header>

        <!-- Video Background Section -->
        <section id="introduction" class="py-5 fade-in" style="position: relative; height: 100vh;">
            <?php if ($introData['media_type'] === 'video' && !empty($introData['video_file'])): ?>
                <video autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
                    <source src="assets/video/intro/<?php echo $introData['video_file']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php elseif ($introData['media_type'] === 'image' && !empty($introData['image_url'])): ?>
                <img src="assets/images/intro/<?php echo $introData['image_url']; ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;" alt="Introduction Image">
            <?php endif; ?>
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
            <div class="container" style="position: relative; z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7 d-flex flex-column text-start justify-content-start">
                        <div>
                            <h1 class="mb-2 tpage m-0 text-light"><?php echo $introData['title1']; ?></h1>
                        </div>
                        <div>
                            <h1 class="mb-4 tpage m-0 text-light"><?php echo $introData['title2']; ?></h1>
                        </div>
                        <div>
                            <p class="text text-light"><?php echo $introData['description']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="about-us" class="py-5 fade-in bg-light ">
            <div class="container">
                <div class="row gx-4 align-items-center justify-content-between">
                    <div class="col-md-5 order-2 order-md-1">
                        <div class="mt-5 mt-md-0 m-0">
                            <span class="m-1 fs-5" style="color: #379777;"><?php echo $aboutUsData['journey_heading']; ?></span>
                            <h2 class="display-5 fw-bold py-1 fs-2"><?php echo $aboutUsData['title']; ?></h2>
                            <p class="lead py-1 fs-6"><?php echo $aboutUsData['description']; ?></p>
                            <p class="lead fs-6"><?php echo $aboutUsData['additional_info']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-5 order-1 order-md-2">
                        <img class="img-fluid rounded-3" style="height: 500px; width: 600px;" src="assets/images/about/<?php echo $aboutUsData['image_url']; ?>" alt="About NovoCamera">
                    </div>
                </div>
            </div>
        </section>

        <section id="blogs-updates" class="py-5 m-0 " style="background-color:F5F7F8">
            <div class="container">
                <div class="text-start">
                    <h3>Blog Updates</h3>
                    <h2 class="display-6">What's happening now</h2>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div></div> <!-- Empty div for alignment purposes -->
                    <a href="Blogs.php" class="btn btn-success ">View All</a>
                </div>

                <!-- Main Blogs Section -->
                <div class="row gx-4 mb-4" id="main-blog-container" style="min-height: 300px;">

                </div>

                <!-- Additional News Section (4 Cards in a Row) -->
                <div id="top-blogs-container" class="row gx-4">
                    <!-- News Item 1 -->

                </div>

        </section>



        <!-- Municipalities Section -->
        <section id="municipalities" class="py-5 bg-dark m-0">
            <div class="container">
                <div class="text-start m-0 mt-3">
                    <h3 class="fs-6 text-uppercase text-light">Discover</h3>
                    <h2 class="display-6">Explore Municipalities</h2>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="All-municipalities.php" class="btn btn-success ms-auto">View All</a>
                </div>
                <?php include 'includes/municipalities.php' ?>
            </div>
        </section>

        <div class="carousel-inner">
            <div class="carousel-item active">
            </div>
        </div>


        <section id="top-places" class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mx-2 mb-5">
                    <div>
                        <h3 class="fs-6 text-uppercase m-0 pb-3 text-warning">Where to Go</h3>
                        <h2 class="display-6 m-0">See Nueva Ecija's open destinations</h2>
                    </div>
                    <a href="places.php" class="btn btn-success ms-auto">View All</a>
                </div>

                <div class="row">
                    <!-- Top Places -->
                    <?php
                    include("connection/connection.php");

                    // Fetch the top 6 places ordered by Place_ID or any other logic (e.g., by popularity, rating, etc.)
                    $sql_places = "SELECT * FROM places ORDER BY Place_ID DESC LIMIT 6";
                    $result_places = $conn->query($sql_places);

                    if ($result_places && $result_places->num_rows > 0) {
                        while ($place = $result_places->fetch_assoc()) {
                            echo "<div class='col-md-4 mb-4'>";
                            echo "<a href='../place-details.php?Place_ID=" . htmlspecialchars($place['Place_ID']) . "' class='card-link'>";
                            echo "<div class='card where-to-go-card border-0 text-white' style='border-radius: 15px; overflow: hidden; width: 100%; height: 350px;'>";
                            echo "<img src='" . htmlspecialchars($place['PlacePicture']) . "' class='card-img' style='height: 100%; object-fit: cover; border-radius: 15px;' alt='" . htmlspecialchars($place['PlaceName']) . "'>";
                            echo "<div class='card-img-overlay where-to-go-overlay d-flex flex-column justify-content-end p-3' style='background: rgba(0, 0, 0, 0.4);'>";
                            echo "<h5 class='card-title mb-0 text-center text-warning'>" . htmlspecialchars($place['PlaceName']) . "</h5>";
                            echo "<p class='card-text text-center text-light'><i class='fas fa-map-marker-alt'></i> " . htmlspecialchars($place['PlaceLocation']) . "</p>";
                            echo "</div></div></a></div>";
                        }
                    } else {
                        echo "<div class='col-12'><p class='text-center text-light'>No top places found.</p></div>";
                    }

                    $conn->close();
                    ?>
                </div>
            </div>
        </section>



        <section id="Activities" class="py-5" style="background-color: #379777;">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mx-2 mb-5">
                    <div>
                        <h3 class="fs-6 text-uppercase m-0 pb-3 text-light">Things to do</h3>
                        <h2 class="display-6 text-light m-0">Something for everyone awaits</h2>
                    </div>
                    <a href="all-categories.php" class="btn btn-success ms-auto">View All</a>
                </div>

                <div class="row">

                    <?php foreach ($categories as $category) { ?>
                        <div class="col-md-4 mb-4">
                            <a href="activities.php?category_id=<?php echo htmlspecialchars($category['category_id']); ?>" class="card-link">
                                <div class="card where-to-go-card border-0 h-100 text-white">
                                    <img src="assets/images/uploads/categories/<?php echo htmlspecialchars($category['image_url']); ?>" class="card-img" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                    <div class="card-img-overlay where-to-go-overlay d-flex flex-column justify-content-end p-3">
                                        <h5 class="card-title mb-0 text-center"><?php echo htmlspecialchars($category['name']); ?></h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </section>




        <!-- Services Section -->
        <section id="services" class="bg-light py-5 m-0">
            <div class="container">
                <div class="text-center mb-5">
                    <h3 class="fs-6 text-uppercase">Our Services</h3>
                    <h2 class="display-6">Explore the Best of Nueva Ecija</h2>
                    <p class="lead">Dive into our curated experiences that bring the history and beauty of Nueva Ecija to life.</p>
                </div>
                <div class="row gy-4">
                    <?php
                    // Fetch services from the database
                    include 'connection/connection.php'; // Update the path as necessary
                    $sql = "SELECT * FROM services";
                    $result = mysqli_query($conn, $sql);

                    // Loop through each service and output it in the same layout
                    while ($service = mysqli_fetch_assoc($result)): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <!-- Dynamically fetch image -->
                                    <img src="assets/images/cms/services/<?php echo $service['service_image']; ?>" alt="<?php echo $service['service_name']; ?>" class="img-fluid mb-4" style="max-width: 80px;">
                                    <!-- Dynamically fetch service name -->
                                    <h4 class="card-title"><?php echo $service['service_name']; ?></h4>
                                    <!-- Dynamically fetch service description -->
                                    <p class="text-muted"><?php echo $service['service_description']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>



        <!-- Explore Section -->
        <section id="explore" style="position: relative; background-image: url('assets/images/farmers-01.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; height: 500px;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; color: white; padding: 20px; box-sizing: border-box;">
                <div style="z-index: 1; text-align: center;">
                    <h2 class="text-light">Ready to Explore?</h2>
                    <p>Find your next adventure with NovoCamera. Discover the hidden gems of Nueva Ecija.</p>
                    <button class="btn btn-success" onclick="location.href='places.php'">Explore Now</button>
                </div>
            </div>
        </section>


        <?php include 'includes/footer.php' ?>

        <Script>
            $(document).ready(function() {

                $.post("ajax_content/add_municipalities.php", {}, function(data) {
                    // Update the HTML content of #Place with the response data
                    $("#Mun-item").html(data);
                    console.log(data);
                });
            });
        </Script>

<script>
    $(document).ready(function() {
        $.ajax({
            url: 'ajax_content/fetch_blogs-index.php', // Update this to the correct PHP file path
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Populate the main blog
                if (response.main_blog) {
                    const mainBlog = response.main_blog;

                    // Strip HTML tags using regex
                    const mainBlogSnippet = mainBlog.content.replace(/<\/?[^>]+(>|$)/g, "").substring(0, 100);

                    // Determine if the blog was uploaded by admin or user
                    const mainImageFolder = mainBlog.uploaded_by === 'admin' ? 'admin' : 'user';

                    const mainBlogHtml = `
                    <div class="row gx-4 mb-4 align-items-start main-blog-card" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        <div class="col-lg-6">
                            <img src="assets/images/blogs/${mainImageFolder}/${mainBlog.image_url}" class="img-fluid rounded" alt="${mainBlog.title}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="col-lg-6 d-flex flex-column justify-content-start">
                            <div>
                                <span class="badge bg-danger">FEATURED NEWS</span>
                                <span class="badge bg-warning">LATEST</span>
                                <h2 class="mt-2">${mainBlog.title}</h2>
                                <p class="text-muted">${new Date(mainBlog.created_at).toLocaleDateString()}</p>
                                <p class="text-muted">${mainBlogSnippet}...</p>
                                <a href="blog-details.php?id=${mainBlog.blog_id}" class="btn btn-success mt-3">Read More</a>
                            </div>
                        </div>
                    </div>
                    `;
                    $('#main-blog-container').html(mainBlogHtml);
                }

                // Populate the top 4 blogs with the provided design
                if (response.top_blogs.length > 0) {
                    let topBlogsHtml = '';
                    response.top_blogs.forEach(blog => {
                        const imageUrlArray = blog.image_url.split(',');
                        const imageUrl = imageUrlArray[0] ? imageUrlArray[0].trim() : 'assets/images/default.jpg';

                        // Determine if the blog was uploaded by admin or user
                        const imageFolder = blog.uploaded_by === 'admin' ? 'admin' : 'user';

                        // Strip HTML tags from content using regex
                        const blogSnippet = blog.content.replace(/<\/?[^>]+(>|$)/g, "").substring(0, 100);

                        topBlogsHtml += `
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 bg-white border border-light shadow-sm attraction-card"
                                style="border-radius: 15px; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                <img src="assets/images/blogs/${imageFolder}/${imageUrl}" alt="${blog.title}" class="card-img-top"
                                    style="border-top-left-radius: 15px; border-top-right-radius: 15px; height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 style="font-weight: 600; font-size: 1.5rem;">${blog.title}</h5>
                                    <p class="text-uppercase text-secondary mb-2" style="font-size: 0.9rem;">${blog.author}</p>
                                    <p class="text-muted" style="font-size: 1rem;">
                                        ${blogSnippet}...
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                                    <a class="btn btn-outline-success" href="blog-details.php?id=${blog.blog_id}"
                                    style="font-weight: 500; font-size: 0.95rem; padding: 0.5rem 1rem; border-radius: 50px;">Read More</a>
                                    <span class="text-muted" style="font-size: 0.85rem;">${blog.comments_count} Comments</span>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                    $('#top-blogs-container').html(topBlogsHtml);
                }
            },
            error: function() {
                console.error('Error fetching blogs.');
            }
        });

    });
</script>



        <script>
            // jQuery to animate cards on scroll
            $(document).ready(function() {
                $('.event-card').each(function(i) {
                    $(this).delay(i * 200).queue(function() {
                        $(this).addClass('animated fadeInUp');
                    });
                });
            });
        </script>



        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="js/main.js"></script>
        <script src="vendors/bootstrap/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
        <script src="js/script/Navascript.js"></script>
        <script src="js/slidemuni.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const sections = document.querySelectorAll('.col-md-5, .col-md-4, .col-lg-3, h2, h3, p');

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in-view');
                        }
                    });
                }, {
                    threshold: 0.1
                });

                sections.forEach(section => {
                    observer.observe(section);
                });
            });
        </script>





    </body>

    </html>