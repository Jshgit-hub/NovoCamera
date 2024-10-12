  <?php session_start(); ?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="vendors/owl-carousel/owl.theme.default.min.css">
    <link rel="stylesheet" href="vendors/owl-carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="css/style-2.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <style>
      /* Cinematic Card Hover Effects */
      .card-hover {
        border-radius: 20px;
        overflow: hidden;
        transition: transform 0.5s ease-in-out, box-shadow 0.5s ease-in-out;
        position: relative;
        height: 100%;
      }

      .card-hover:hover {
        transform: translateY(-10px);
        box-shadow: 0px 20px 40px rgba(0, 0, 0, 0.3);
      }

      .card-hover .card-img-wrapper {
        overflow: hidden;
        position: relative;
        height: 100%;
      }

      .card-hover .card-img {
        transition: transform 0.7s ease-in-out;
        height: 100%;
      }

      .card-hover:hover .card-img {
        transform: scale(1.1);
        /* Cinematic Zoom-in effect */
      }

      .overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 50px;
        background: rgba(0, 0, 0, 0.6);
        /* Dark overlay at the bottom */
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.5s ease;
      }

      .card-hover:hover .overlay {
        background: rgba(0, 0, 0, 0.85);
        /* Darken overlay on hover */
      }

      .card-title {
        font-size: 1.4rem;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        transition: transform 0.4s ease, color 0.4s ease;
        color: #fff;
        padding: 0.5rem;
      }

      .card-hover:hover .card-title {
        transform: scale(1.1);
        /* Cinematic scaling */
        color: #f39c12;
        /* Cinematic color change */
      }

      /* Hero Section for Cinematic Look */
      .hero-section {
        position: relative;
        height: 100vh;
        overflow: hidden;
      }

      .hero-section img {
        width: 100%;
        height: 100vh;
        object-fit: cover;
        filter: brightness(70%);
      }

      .carousel-caption {
        position: absolute;
        bottom: 20%;
        left: 5%;
        right: 5%;
        text-align: left;
        z-index: 10;
        animation: fadeInUp 1s ease-out both;
      }

      .carousel-caption h1 {
        font-size: 3rem;
        font-weight: bold;
        text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
      }

      .carousel-caption p {
        font-size: 1.5rem;
        color: #f8f9fa;
        text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
      }

      /* Animation for Carousel Text */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(40px);
        }

        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Cinematic Scroll Fade-in Animation */
      .fade-in {
        opacity: 0;
        transition: opacity 0.8s ease-out, transform 0.8s ease-out;
      }

      .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
      }

      .fade-in.hidden {
        transform: translateY(50px);
      }

      /* Styling for District Titles */
      .district-title {
        font-size: 2.5rem;
        font-weight: bold;
        text-transform: uppercase;
        color: #333;
        margin-bottom: 20px;
      }

      .bg-dark .district-title {
        color: #f39c12;
        /* Contrasting color for dark background */
      }

      .bg-success .district-title {
        color: #ffffff;
      }
    </style>
  </head>

  <body class="">

    <header class="header_area">
      <div class="main_menu">
        <?php include 'includes/navbar.php'; ?>
      </div>
    </header>


    <section class="hero-section mb-5">
      <div class="container-fluid p-0">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="assets/images/banner/banner-blogs/tradition.jpg" class="d-block w-100" alt="Beautiful Nueva Ecija">
              <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4 text-light">Welcome to Nueva Ecija</h1>
                <p class="lead">Explore the beauty and culture of Nueva Ecija</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="assets/images/banner/banner-blogs/hike.jpg" class="d-block w-100" alt="Adventure Awaits">
              <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4 text-light">Adventure Awaits</h1>
                <p class="lead">Discover breathtaking trails and natural wonders</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="assets/images/banner/banner-blogs/culture.jpg" class="d-block w-100" alt="Cultural Heritage">
              <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4 text-light">Cultural Heritage</h1>
                <p class="lead">Dive into the rich history and traditions</p>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Breadcrumb and Title Section -->
    <section class="container mt-5 ">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb" style="background-color: #f1f1f1; padding: 10px; border-radius: 8px;">
          <li class="breadcrumb-item">
            <a href="#" class="text-success" style="color: #379777;">Home</a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" class="text-success" style="color: #379777;">Where to Go</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page" style="color: #45474B;">By District</li>
        </ol>
      </nav>

      <h1 class="display-5 fw-bold text-dark">More than a hundred wonders</h1>

    </section>

    <!-- District Sections -->
    <section class="container mt-4">
      <h3 class="fs-1 my-3">First District</h3>
      <div class="row" id="first-district">
        <!-- Municipalities will be loaded here -->
      </div>
    </section>

    <div class="container-fluid bg-dark">
      <section class="container mt-4 py-5">
        <h3 class="fs-1 my-3 text-warning ">Second District</h3>
        <div class="row" id="second-district">
          <!-- Municipalities will be loaded here -->
        </div>
      </section>
    </div>

    <section class="container mt-4">
      <h3 class="fs-1 my-3">Third District</h3>
      <div class="row" id="third-district">
        <!-- Municipalities will be loaded here -->
      </div>
    </section>

    <div class="container-fluid" style="background-color: #379777;">
      <section class="container mt-4 py-5">
        <h3 class="fs-1 my-3 text-light">Fourth District</h3>
        <div class="row" id="fourth-district">
          <!-- Municipalities will be loaded here -->
        </div>
      </section>
    </div>
    <?php include 'includes/footer.php'; ?>

    <script src="vendors/owl-carousel/owl.carousel.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      $(document).ready(function() {
        // Function to fetch and display municipalities for each district
        function fetchDistrictMunicipalities(districtID, elementID) {
          $.ajax({
            url: 'ajax_content/fetch-all-municipality.php',
            type: 'GET',
            data: {
              district_id: districtID
            },
            success: function(response) {
              $(elementID).html(response);
            },
            error: function() {
              $(elementID).html('<p class="text-center text-danger">Failed to load municipalities.</p>');
            }
          });
        }

        // Fetch municipalities for each district
        fetchDistrictMunicipalities(1, '#first-district');
        fetchDistrictMunicipalities(2, '#second-district');
        fetchDistrictMunicipalities(3, '#third-district');
        fetchDistrictMunicipalities(4, '#fourth-district');
      });
    </script>

  </body>

  </html>