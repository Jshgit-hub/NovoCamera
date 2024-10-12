<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Nueva Ecija Tourism - Blogs</title>
  <link rel="icon" href="img/Fevicon.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <link rel="stylesheet" href="css/style-2.css">
  <style>
    body {
      color: #555;
      background-color: #f8f9fa;
      overflow-x: hidden;
      scroll-behavior: smooth;
    }

    .hero-banner {
      background: url('assets/images/bg_1.jpg') center center/cover no-repeat;
      height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      background-attachment: fixed;
    }

    .hero-banner::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1;
    }

    .hero-banner .content {
      z-index: 2;
    }

    .hero-section .carousel-item {
      height: 50vh;
      /* Set the height to 50% of the viewport height */
    }

    .hero-section .carousel-item img {
      object-fit: cover;
      /* Ensure the image covers the entire area */
      height: 100%;
      /* Ensure the image takes up the full height of the carousel item */
      width: 100%;
      /* Ensure the image takes up the full width */
    }

    .carousel-caption {
      background: rgba(0, 0, 0, 0.5);
      /* Add a semi-transparent background to the captions */
      padding: 1rem;
      /* Add some padding to the caption */
      border-radius: 10px;
      /* Slightly round the corners of the caption */
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header class="header_area">
    <div class="main_menu">
      <?php include 'includes/navbar.php'; ?>
    </div>
  </header>

  <!-- Hero Section with Banner -->
  <section class="hero-banner position-relative d-flex justify-content-center align-items-center text-center">
    <div class="content animate__animated animate__fadeInDown">
    </div>
  </section>

  <section class="container mt-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-success">HOME</a></li>
        <li class="breadcrumb-item active" aria-current="page">Categories</li>
      </ol>
    </nav>
  </section>

  

  <!-- All Blogs Section -->
  <section class="blog-section py-5">
    <div class="container">
      <div class="text-start mb-5">
        <h2 class="display-5 fw-bold">For you</h2>
      </div>
      <div class="row" id="category List">
        <!-- Blog posts will be loaded here based on the current page -->
        <?php include 'ajax_content/nofucntion'; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <script>
    $(document).ready(function() {
      // Event listener for pagination links
      $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        fetchBlogs(page);
      });

      function fetchBlogs(page = 1) {
        $.ajax({
          url: 'ajax_content/.php',
          type: 'POST',
          data: {
            page: page
          },
          success: function(response) {
            $('#blogList').html(response); // Update blog list with fetched content
          },
          error: function() {
            alert('An error occurred while fetching blogs.');
          }
        });
      }

      // Initial fetch on page load
      fetchBlogs();
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="js/main.js"></script>
</body>

</html>