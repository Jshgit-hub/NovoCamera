<?php
session_start();
include 'connection/connection.php';
$query = "SELECT DISTINCT blog_type FROM blogs WHERE status = 'published'";
$result = $conn->query($query);
$options = ''; // Prepare a variable to store options

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $options .= "<option value='" . htmlspecialchars($row['blog_type']) . "'>" . htmlspecialchars($row['blog_type']) . "</option>";
  }
} else {
  $options = "<option value=''>No blog types available</option>";
}
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
      font-family: 'Poppins', sans-serif;
    }

    /* Hero Section */
    .hero-banner {
      background: url('assets/images/bg_1.jpg') center center/cover no-repeat;
      height: 70vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      background-attachment: fixed;
      position: relative;
      z-index: 1;
      overflow: hidden;
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
      opacity: 0;
      animation: fadeInUp 1.5s ease-in-out forwards;
    }

    .hero-banner h1 {
      font-size: 3.5rem;
      font-weight: bold;
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
    }

    .hero-banner p {
      font-size: 1.25rem;
      font-weight: 300;
      margin-top: 10px;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Blog Card Effects */
    .attraction-card {
      border-radius: 15px;
      overflow: hidden;
      background-color: #fff;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.5s ease-in-out;
      cursor: pointer;
      text-decoration: none;
      position: relative;
      margin-bottom: 30px;
      opacity: 0;
      transform: scale(0.9);
      transition: transform 0.5s ease, opacity 0.5s ease;
    }

    .attraction-card.in-view {
      opacity: 1;
      transform: scale(1);
    }

    .attraction-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      transition: transform 0.5s ease-in-out;
    }

    .attraction-card:hover img {
      transform: scale(1.1);
    }

    .attraction-title {
      font-size: 1.75rem;
      font-weight: bold;
      margin-top: 15px;
      color: #333;
    }

    .attraction-location {
      color: #999;
      text-transform: uppercase;
      font-size: 0.875rem;
    }

    .attraction-description {
      margin-top: 10px;
      font-size: 1rem;
      color: #666;
      line-height: 1.6;
    }

    .attraction-meta {
      font-size: 0.875rem;
      color: #777;
      margin-top: 10px;
    }

    /* Section Headings */
    .blog-section h2 {
      font-size: 2.5rem;
      font-weight: bold;
      color: #333;
      text-transform: uppercase;
      animation: fadeInDown 1s ease-in-out;
    }

    .blog-section p {
      font-size: 1.1rem;
      color: #777;
      margin-bottom: 1.5rem;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-40px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Breadcrumb */
    .breadcrumb-item a {
      color: #28a745;
      transition: color 0.3s ease-in-out;
    }

    .breadcrumb-item a:hover {
      color: #1c7430;
    }

    /* Pagination Effects */
    .pagination .page-link {
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .pagination .page-link:hover {
      background-color: #28a745;
      color: #fff;
    }

    /* Responsive Styling */
    @media (max-width: 768px) {
      .hero-banner h1 {
        font-size: 2.5rem;
      }

      .hero-banner p {
        font-size: 1rem;
      }

      .attraction-card img {
        height: 200px;
      }
    }

    @media (max-width: 576px) {
      .hero-banner h1 {
        font-size: 2rem;
      }

      .hero-banner p {
        font-size: 0.875rem;
      }

      .attraction-card img {
        height: 150px;
      }
    }

    /* General styling for input and select */
.search-input, .filter-select {
    border-radius: 50px; /* Fully rounded corners */
    background-color: #f3f4f6; /* Subtle background color */
    border: 1px solid #ccc; /* Light border */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Soft shadow for depth */
    transition: all 0.3s ease; /* Smooth transition for hover and focus */
}

/* Focus and hover state */
.search-input:focus, .filter-select:focus {
    background-color: #fff; /* White background on focus */
    border-color: #379777; /* Highlight border color on focus */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Stronger shadow on focus */
    outline: none; /* Remove default focus outline */
}

.search-input:hover, .filter-select:hover {
    border-color: #379777; /* Change border color on hover */
}

/* Custom padding for search input to accommodate icon */
.search-input {
    padding-left: 2.5rem; /* Padding to fit the search icon inside */
}

/* Custom padding for filter select */
.filter-select {
    padding-left: 1rem;
}

/* Styling the search icon */
.fas.fa-search {
    color: #999; /* Light grey color for the icon */
    font-size: 1.2rem;
}

/* Hover effect for the search icon */
.search-input:hover ~ .fas.fa-search {
    color: #379777; /* Change icon color on hover */
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
    <div class="content">
      <h1>Discover Nueva Ecija Through Our Blogs</h1>
      <p>Explore the best stories, events, and attractions</p>
    </div>
  </section>

  <section class="container mt-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-success">HOME</a></li>
        <li class="breadcrumb-item active" aria-current="page"> BLOGS AND EVENTS</li>
      </ol>
    </nav>
  </section>

  <!-- Top Blogs Section -->
  <section class="blog-section py-5 ">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Top Blogs</h2>
        <p>Check out the most popular stories about Nueva Ecija</p>
      </div>
      <div class="row">
        <?php include 'ajax_content/fetch_top_blogs.php'; ?>
      </div>
    </div>
  </section>



  <!-- All Blogs Section -->
  <section class="blog-section py-5">
    <div class="container">
      <div class="text-start mb-5">
        <h2 class="display-5 fw-bold">Explore More</h2>

        <!-- Search and Filter Section -->
        <div class="row mb-4">
          <!-- Search Input -->
          <div class="col-md-6 position-relative">
            <input type="text" class="form-control ps-5 py-2 search-input" id="searchInput" placeholder="Search blogs...">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3" style="z-index: 2;">
              <i class="fas fa-search text-muted"></i>
            </span>
          </div>

          <!-- Filter Select -->
          <div class="col-md-6">
            <div class="dropdown">
              <select class="form-select py-2 filter-select" id="filterSelect" aria-label="Blog Type Filter">
                <option value="All">All Types</option>
                <?php echo $options; ?>
              </select>
            </div>
          </div>
        </div>


      </div>

      <div class="row" id="blogList">
        <!-- Blog posts will be loaded here based on the current page, search, and filter -->
        <?php include 'ajax_content/fetch_blogs.php'; ?>
      </div>
    </div>
  </section>


  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <script>
    $(document).ready(function() {
      // Fetch initial blogs on page load
      fetchBlogs();

      // Event listener for pagination links
      $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        fetchBlogs(page);
      });

      // Event listener for search input
      $('#searchInput').on('input', function() {
        fetchBlogs(1); // Fetch blogs from the first page when searching
      });

      // Event listener for filter select dropdown
      $('#filterSelect').on('change', function() {
        fetchBlogs(1); // Fetch blogs from the first page when filtering
      });

      // Function to fetch blogs based on search, filter, and pagination
      function fetchBlogs(page = 1) {
        var searchQuery = $('#searchInput').val();
        var filter = $('#filterSelect').val();

        $.ajax({
          url: 'ajax_content/fetch_blogs.php',
          type: 'POST',
          data: {
            page: page,
            search: searchQuery,
            filter: filter
          },
          success: function(response) {
            $('#blogList').html(response); // Update blog list with fetched content
            animateCards(); // Apply animation after content is loaded
          },
          error: function() {
            alert('An error occurred while fetching blogs.');
          }
        });
      }

      // Function to add animation to blog cards
      function animateCards() {
        const cards = document.querySelectorAll('.attraction-card');
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('in-view');
            }
          });
        }, {
          threshold: 0.1
        });

        cards.forEach(card => {
          observer.observe(card);
        });
      }

      animateCards(); // Apply the animation on page load
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/main.js"></script>
</body>

</html>