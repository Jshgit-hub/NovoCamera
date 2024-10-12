<?php session_start();

include 'connection/connection.php';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Nueva Ecija Tourism - Explore</title>
  <link rel="icon" href="img/Fevicon.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
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
      height: 100vh;
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

    .hero-banner h1,
    .hero-banner p {
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .explore-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .explore-card:hover {
      transform: translateY(-10px);
      box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
    }

    .video-section {
      position: relative;
      height: 70vh;
      background: #000;
      overflow: hidden;
    }

    .video-section video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.6;
    }

    .video-section .content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      text-align: center;
      z-index: 2;
    }

    .card-activity {
      background-size: cover;
      background-position: center;
      color: white;
      padding: 50px 20px;
      border-radius: 10px;
      position: relative;
      text-align: center;
      height: 300px;
      transition: transform 0.3s ease;
    }

    .card-activity h5 {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .card-activity::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      border-radius: 10px;
      transition: background 0.3s ease;
    }

    .card-activity:hover {
      transform: translateY(-10px);
    }

    .card-activity:hover::before {
      background: rgba(0, 0, 0, 0.6);
    }
  </style>
</head>

<body>
  <header class="header_area">
    <div class="main_menu">
      <?php include 'includes/navbar.php' ?>
    </div>
  </header>

  <!--================ Hero Banner =================-->
  <section class="hero-banner position-relative d-flex justify-content-center align-items-center text-center">
    <div class="content animate__animated animate__fadeInDown">
      <h1 class="text-light display-4 fw-bold">Explore Nueva Ecija</h1>
      <p class="animate__animated animate__fadeInUp">Discover the beauty, history, and adventure in every corner of Nueva Ecija.</p>
      <a href="#activities" class="btn btn-success">Start Your Journey</a>
    </div>
  </section>
  <!--================ Hero Banner =================-->

  <!--================ Video Background Section =================-->
  <section class="video-section">
    <video autoplay muted loop>
      <source src="assets/video/Gabaldon Nueva Ecija _ 4k Aerial Footage.mp4" type="video/mp4">
      Your browser does not support HTML5 video.
    </video>
    <div class="content">
      <h2 class="animate__animated animate__fadeInLeft text-light display-5 fw-bold">
        Immerse Yourself in <span style="color: #28a745;">Nature</span>
      </h2>
    </div>
  </section>

  <!--================ Video Background Section =================-->

  <!--================ Activities Section =================-->
  <section id="activities" class="py-5 bg-dark">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Activities</h2>
        <p class="text-light">Find exciting things to do in Nueva Ecija</p>
      </div>
      <div class="row text-center">
        <div class="col-md-3">
          <div class="card-activity" style="background-image: url('assets/images/activities/hiking.jpg');">
            <h5 class="text-light">Hiking</h5>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-activity" style="background-image: url('assets/images/activities/swimming.jpg');">
            <h5 class="text-light">Swimming</h5>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-activity" style="background-image: url('assets/images/activities/cycling.jpg');">
            <h5 class="text-light">Cycling</h5>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-activity" style="background-image: url('assets/images/activities/photography.jpg');">
            <h5 class="text-light">Photography</h5>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!--================ Activities Section =================-->

  <section class="py-5 bg-dark">
    <div class="container text-center">
      <h3 class="display-5 fw-bold mb-4">Explore Nueva Ecija on the Map</h3>

      <!-- Dropdown for Categories -->
      <div class="dropdown mb-4">
        <button class="btn btn-success dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          Select Category
        </button>
        <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
          <li><a class="dropdown-item" href="#" data-category="all">All</a></li>
          <li><a class="dropdown-item" href="#" data-category="historical">Historical Sites</a></li>
          <li><a class="dropdown-item" href="#" data-category="natural">Natural Attractions</a></li>
          <li><a class="dropdown-item" href="#" data-category="restaurants">Restaurants</a></li>
        </ul>
      </div>

      <div id="map" style="height: 500px; border-radius: 10px;"></div>
    </div>
  </section>

  <!--================ Interactive Map Section =================-->

  <!--================ Historical Timeline Section =================-->
<!--================ Historical Timeline Section =================-->
<section class="historical-timeline-section py-5" style="position: relative; background-image: url('assets/images/timeline/background.jpg'); background-size: cover; background-position: center;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px);"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="text-center mb-5">
            <h2 style="font-family: 'Poppins', sans-serif; color: #ffffff; font-size: 2.5rem;">Historical Timeline of Nueva Ecija</h2>
            <p style="color: #ffffff; font-family: 'Montserrat', sans-serif; font-style: italic;">Journey through the significant events that shaped the province's rich history.</p>
        </div>

        <div class="timeline">
            <!-- Timeline Item 1: Early Settlements -->
            <div class="row mb-5">
                <div class="col-md-6 text-md-end text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <i class="fas fa-leaf fa-2x me-3" style="color: #28a745;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">Early Settlements - 1500s</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">The early settlers of Nueva Ecija laid the foundation for a province rich in culture and history.</p>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <img src="assets/images/timeline/early-settlements.jpg" alt="Early Settlements" class="img-fluid rounded shadow">
                </div>
            </div>

            <!-- Timeline Item 2: Spanish Colonization -->
            <div class="row mb-5">
                <div class="col-md-6 order-md-2 text-md-start text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-start mb-3">
                            <i class="fas fa-church fa-2x me-3" style="color: #ffc107;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">Spanish Colonization - 1600s</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">Nueva Ecija was formally established as a province by the Spanish, bringing new governance and cultural influences.</p>
                    </div>
                </div>
                <div class="col-md-6 order-md-1 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <img src="assets/images/timeline/spanish-colonization.jpg" alt="Spanish Colonization" class="img-fluid rounded shadow">
                </div>
            </div>

            <!-- Timeline Item 3: Philippine Revolution -->
            <div class="row mb-5">
                <div class="col-md-6 text-md-end text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <i class="fas fa-fist-raised fa-2x me-3" style="color: #dc3545;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">Philippine Revolution - 1896</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">The people of Nueva Ecija played a significant role in the Philippine Revolution, fighting for independence from Spanish rule.</p>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <img src="assets/images/timeline/philippine-revolution.jpg" alt="Philippine Revolution" class="img-fluid rounded shadow">
                </div>
            </div>

            <!-- Timeline Item 4: American Period -->
            <div class="row mb-5">
                <div class="col-md-6 order-md-2 text-md-start text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-start mb-3">
                            <i class="fas fa-school fa-2x me-3" style="color: #007bff;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">American Period - 1900s</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">During the American occupation, Nueva Ecija saw the introduction of new educational systems and infrastructure development.</p>
                    </div>
                </div>
                <div class="col-md-6 order-md-1 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <img src="assets/images/timeline/american-period.jpg" alt="American Period" class="img-fluid rounded shadow">
                </div>
            </div>

            <!-- Timeline Item 5: World War II -->
            <div class="row mb-5">
                <div class="col-md-6 text-md-end text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <i class="fas fa-flag-usa fa-2x me-3" style="color: #17a2b8;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">World War II - 1940s</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">Nueva Ecija became a site of conflict during World War II, with many locals contributing to the resistance against Japanese forces.</p>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <img src="assets/images/timeline/world-war-ii.jpg" alt="World War II" class="img-fluid rounded shadow">
                </div>
            </div>

            <!-- Timeline Item 6: Post-War Era -->
            <div class="row mb-5">
                <div class="col-md-6 order-md-2 text-md-start text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-start mb-3">
                            <i class="fas fa-tractor fa-2x me-3" style="color: #28a745;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">Post-War Era - 1950s</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">In the years following World War II, Nueva Ecija focused on rebuilding and developing its agricultural sector, becoming known as the "Rice Granary of the Philippines."</p>
                    </div>
                </div>
                <div class="col-md-6 order-md-1 d-flex align-items-center justify-content-center mb-4 mb-md-0">
                    <img src="assets/images/timeline/post-war-era.jpg" alt="Post-War Era" class="img-fluid rounded shadow">
                </div>
            </div>

            <!-- Timeline Item 7: Modern Nueva Ecija -->
            <div class="row mb-5">
                <div class="col-md-6 text-md-end text-center">
                    <div class="timeline-content p-4" style="background-color: rgba(255, 255, 255, 0.9); color: #333; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <i class="fas fa-seedling fa-2x me-3" style="color: #28a745;"></i>
                            <h5 class="timeline-title m-0" style="font-family: 'Poppins', sans-serif;">Modern Nueva Ecija - 2000s to Today</h5>
                        </div>
                        <p style="font-family: 'Montserrat', sans-serif;">Today, Nueva Ecija continues to thrive, balancing its rich historical heritage with modern advancements in agriculture, education, and tourism.</p>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <img src="assets/images/timeline/modern-nueva-ecija.jpg" alt="Modern Nueva Ecija" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>
<!--================ Historical Timeline Section =================-->


  <!--================ Historical Timeline Section =================-->

  <!--================ Blog Section =================-->
  <section class="blog-section py-5 bg-dark">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Travel guides</h2>
        <p>Explore the latest stories and guides about Nueva Ecija</p>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="single-recent-blog-post card-view">
            <div class="thumb">
              <img class="card-img rounded-0" src="assets/images/blogs/hidden.jpg" alt="Blog 1">
              <ul class="thumb-info list-unstyled d-flex">
                <li class="me-3"><a href="#"><i class="ti-user"></i>Admin</a></li>
                <li><a href="#"><i class="ti-themify-favicon"></i>2 Comments</a></li>
              </ul>
            </div>
            <div class="details mt-3">
              <a href="blog-single.html">
                <h3>Exploring the Hidden Gems of Nueva Ecija</h3>
              </a>
              <p>Discover the lesser-known but equally breathtaking locations in Nueva Ecija.</p>
              <a class="button" href="#">Read More <i class="ti-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="single-recent-blog-post card-view">
            <div class="thumb">
              <img class="card-img rounded-0" src="assets/images/blogs/local.jpg" alt="Blog 2">
              <ul class="thumb-info list-unstyled d-flex">
                <li class="me-3"><a href="#"><i class="ti-user"></i>Admin</a></li>
                <li><a href="#"><i class="ti-themify-favicon"></i>3 Comments</a></li>
              </ul>
            </div>
            <div class="details mt-3">
              <a href="blog-single.html">
                <h3>A Day in the Life of a Nueva Ecija Local</h3>
              </a>
              <p>Experience the daily life and traditions of Nueva Ecija residents.</p>
              <a class="button" href="#">Read More <i class="ti-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="single-recent-blog-post card-view">
            <div class="thumb">
              <img class="card-img rounded-0" src="assets/images/bg_1.jpg" alt="Blog 3">
              <ul class="thumb-info list-unstyled d-flex">
                <li class="me-3"><a href="#"><i class="ti-user"></i>Admin</a></li>
                <li><a href="#"><i class="ti-themify-favicon"></i>5 Comments</a></li>
              </ul>
            </div>
            <div class="details mt-3">
              <a href="blog-single.html">
                <h3>Top 10 Must-Visit Places in Nueva Ecija</h3>
              </a>
              <p>Here’s a list of must-visit destinations in Nueva Ecija for your next trip.</p>
              <a class="button" href="#">Read More <i class="ti-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <nav aria-label="Page navigation example" class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </section>
  <!--================ Blog Section =================-->

  <!--================ Start Footer Area =================-->
  <?php include 'includes/footer.php' ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize the map
      var map = L.map('map').setView([15.5812, 120.8486], 10);

      // Add OpenStreetMap tiles
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      // Custom icons
      var historicalIcon = L.icon({
        iconUrl: 'assets/Images/icon/coliseum.png',
        iconSize: [32, 32],
        className: 'custom-icon'
      });

      var naturalIcon = L.icon({
        iconUrl: 'assets/Images/icon/landscape.png',
        iconSize: [32, 32],
        className: 'custom-icon'
      });

      var restaurantIcon = L.icon({
        iconUrl: 'assets/Images/icon/restaurant.png',
        iconSize: [32, 32],
        className: 'custom-icon'
      });

      // Define markers with categories and custom icons
      var places = [{
          lat: 15.5828,
          lng: 120.8486,
          category: 'historical',
          icon: historicalIcon,
          popup: '<div class="custom-popup">Historical Site 1</div>'
        },
        {
          lat: 15.5871,
          lng: 120.8500,
          category: 'historical',
          icon: historicalIcon,
          popup: '<div class="custom-popup">Historical Site 2</div>'
        },
        {
          lat: 15.5724,
          lng: 120.8509,
          category: 'natural',
          icon: naturalIcon,
          popup: '<div class="custom-popup">Natural Attraction 1</div>'
        },
        {
          lat: 15.5739,
          lng: 120.8443,
          category: 'natural',
          icon: naturalIcon,
          popup: '<div class="custom-popup">Natural Attraction 2</div>'
        },
        {
          lat: 15.5743,
          lng: 120.8494,
          category: 'restaurants',
          icon: restaurantIcon,
          popup: '<div class="custom-popup">Restaurant 1</div>'
        },
        {
          lat: 15.5800,
          lng: 120.8502,
          category: 'restaurants',
          icon: restaurantIcon,
          popup: '<div class="custom-popup">Restaurant 2</div>'
        }
      ];

      // Add markers to the map with custom popups
      var markers = [];
      places.forEach(function(place) {
        var marker = L.marker([place.lat, place.lng], {
            icon: place.icon
          })
          .bindPopup(place.popup, {
            className: 'animate__animated animate__fadeIn'
          })
          .addTo(map);
        marker.category = place.category;
        markers.push(marker);
      });

      // Filter markers based on category
      document.querySelectorAll('.dropdown-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          var category = this.getAttribute('data-category');

          markers.forEach(function(marker) {
            if (category === 'all' || marker.category === category) {
              map.addLayer(marker);
            } else {
              map.removeLayer(marker);
            }
          });
        });
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      const timelineItems = document.querySelectorAll('.timeline-content');

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      }, {
        threshold: 0.1 // Trigger when 10% of the element is in view
      });

      timelineItems.forEach(item => {
        observer.observe(item);
      });
    });
  </script>
</body>

</html>
