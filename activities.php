<?php
session_start();
include 'connection/connection.php'; // Adjust the path as necessary

// Get the category_id from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Fetch activities that match the category_id
$sql = "SELECT activity_id, name, description, location, latitude, longitude, image_url FROM activities WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}

// Fetch the category name for the breadcrumb
$sql_category = "SELECT name FROM categories WHERE category_id = ?";
$stmt_category = $conn->prepare($sql_category);
$stmt_category->bind_param("i", $category_id);
$stmt_category->execute();
$result_category = $stmt_category->get_result();
$category = $result_category->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Activities in <?php echo htmlspecialchars($category['name']); ?></title>
  <link rel="icon" href="img/Fevicon.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="css/style-2.css">
  <style>
    body {
      color: #555;
      background-color: #f8f9fa;
    }

    .activity-card {
      position: relative;
      overflow: hidden;
      border-radius: 15px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 300px; /* Consistent height */
      width: 100%;
    }

    .activity-card img {
      object-fit: cover;
      height: 100%;
      width: 100%;
      transition: transform 0.3s ease;
    }

    .activity-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .activity-card:hover img {
      transform: scale(1.1);
    }

    .card-link {
      text-decoration: none;
      color: inherit; /* Keep text color for the link */
    }

    .card-link:hover {
      text-decoration: none;
    }

    .breadcrumb {
      background-color: transparent;
    }


    .breadcrumb-item + .breadcrumb-item::before {
      content: ">";
      color: #379777;
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

  <nav aria-label="breadcrumb" class="container mt-4">
  <ol class="breadcrumb" style="background-color: #f1f1f1; padding: 10px; border-radius: 8px;">
    <li class="breadcrumb-item">
      <a href="index.php" style="color: #379777;">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="all-categories.php" style="color: #379777;">Categories</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page" style="color: #45474B;"><?php echo htmlspecialchars($category['name']); ?></li>
  </ol>
</nav>


  <!-- Activities Section -->
  <section class="py-5">
    <div class="container">
      <div class="text-start mb-5">
        <h2 class="display-5 fw-bold">Activities in <?php echo htmlspecialchars($category['name']); ?></h2>
      </div>
      <div class="row">
        <?php if (empty($activities)) { ?>
          <p>No activities found for this category.</p>
        <?php } else { ?>
          <?php foreach ($activities as $activity) { ?>
            <div class="col-md-4 mb-4">
              <a href="activity-details.php?activity_id=<?php echo $activity['activity_id']; ?>" class="card-link">
                <div class="card activity-card border-0 h-100">
                  <img src="assets/images/uploads/activities/<?php echo htmlspecialchars($activity['image_url']); ?>" class="card-img" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                  <div class="card-body">
                    <h5 class="card-title text-success"><?php echo ($activity['name']); ?></h5>
                    <p class="card-text">
                      <?php 
                        // Truncate description to 100 characters and add "..."
                        $snippet = substr($activity['description'], 0, 100);
                        echo ($snippet) . '...';
                      ?>
                    </p>
                    <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($activity['location']); ?></small></p>
                  </div>
                </div>
              </a>
            </div>
          <?php } ?>
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
