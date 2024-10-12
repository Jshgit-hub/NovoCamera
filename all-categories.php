<?php
session_start();
include 'connection/connection.php'; // Adjust the path as necessary

// Fetch all categories
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
    <title>Nueva Ecija Tourism - All Categories</title>
    <link rel="icon" href="img/Fevicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style-2.css">

    <style>
        body {
            color: #555;
            background-color: #f8f9fa;
        }

        .hero-banner {
      background: url('assets/images/bg_1.jpg') center center/cover no-repeat;
      height: 70vh;
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

        .where-to-go-card {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 300px;
            /* Consistent height */
            width: 100%;
            /* Full width */
        }

        .card-link {
            text-decoration: none;
        }

        .card-img {
            object-fit: cover;
            height: 100%;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .where-to-go-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));
            padding: 20px;
            transition: background 0.3s ease;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
        }

        .where-to-go-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .where-to-go-card:hover .card-img {
            transform: scale(1.1);
        }

        .where-to-go-card:hover .where-to-go-overlay {
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.3));
        }

        .card-title:hover {
            text-decoration: underline;
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


    <section class="hero-banner position-relative d-flex justify-content-center align-items-center text-center">
    <div class="content animate__animated animate__fadeInDown">
    </div>
  </section>

    <!-- All Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-start mb-5">
                <h2 class="display-5 fw-bold">All Categories</h2>
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

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
</body>

</html>