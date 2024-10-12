<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

// Check if the connection was successful
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Function to check if a given page should be marked as active
function isPageActive($pageName)
{
  $currentPage = basename($_SERVER['PHP_SELF']);
  return ($currentPage === $pageName) ? 'active' : '';
}

// Fetch the current logo from the database
$logoResult = mysqli_query($conn, "SELECT logo_url FROM site_logo ORDER BY id DESC LIMIT 1");
$logoData = mysqli_fetch_assoc($logoResult);

// Set a default logo if no logo is found in the database
$current_logo = $logoData['logo_url'] ?? 'default_logo.svg';

$isLoggedIn = isset($_SESSION['username']); // Check if the user is logged in
$username = $_SESSION['username'] ?? ''; // Get the username if logged in

$profilePic = 'default-profile.png'; // Default profile picture
$unreadNotifications = 0; // Initialize unread notifications count

$navbarCategoriesResult = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");

// Store the fetched categories in a new array
$navbarCategories = mysqli_fetch_all($navbarCategoriesResult, MYSQLI_ASSOC);

if ($isLoggedIn) {
  $user_id = (int)$_SESSION['user_id'];

  // Fetch profile picture
  $query = "SELECT profile_picture FROM users WHERE user_id = $user_id";
  $result = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if (!empty($row['profile_picture'])) {
      $profilePic = $row['profile_picture']; // Use the user's profile picture
    }
  }

  // Fetch unread notifications count
  $unreadCountQuery = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = $user_id AND is_read = 0";
  $unreadCountResult = mysqli_query($conn, $unreadCountQuery);

  if ($unreadCountResult) {
    $row = mysqli_fetch_assoc($unreadCountResult);
    $unreadNotifications = $row['unread_count'];
  }

  // Fetch latest notifications
  $notificationsQuery = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
  $notificationsResult = mysqli_query($conn, $notificationsQuery);
}
?>

<header class="header_area">
  <div class="main_menu bg-dark">
    <nav class="navbar navbar-expand-lg navbar-light d-flex align-items-end">
      <div class="container box_1620">
        <!-- Brand logo -->
        <a class="navbar-brand logo_h" href="index.php">
          <img src="assets/images/<?php echo $current_logo; ?>" alt="Logo">
        </a>

        <!-- Navbar toggler for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar items -->
        <div class="collapse navbar-collapse offset" id="navbarSupportedContent">
          <!-- Left-aligned nav items -->
          <ul class="navbar-nav me-auto">
            <li class="nav-item <?php echo isPageActive('index.php'); ?>">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item <?php echo isPageActive('places.php'); ?>">
              <a class="nav-link" href="places.php">Explore</a>
            </li>

            <!-- Dropdown for "Where to go" -->
            <li class="nav-item dropdown <?php echo isPageActive('Interactive-map.php') || isPageActive('All-municipalities.php') ? 'active' : ''; ?>">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Where to go
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="All-municipalities.php">By district</a></li>
              </ul>
            </li>

            <!-- Dropdown for "What to do" dynamically fetching categories -->
            <li class="nav-item dropdown <?php echo isPageActive('activities.php') || isPageActive('') ? 'active' : ''; ?>">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                What to do
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <?php foreach ($navbarCategories as $navbarCategory): ?>
                  <li><a class="dropdown-item" href="activities.php?category_id=<?php echo htmlspecialchars($navbarCategory['category_id']); ?>"><?php echo htmlspecialchars($navbarCategory['name']); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>

            <!-- Additional menu items -->
            <li class="nav-item <?php echo isPageActive('Blogs.php'); ?>">
              <a class="nav-link" href="Blogs.php">Blogs</a>
            </li>
            <li class="nav-item <?php echo isPageActive('gallery.php'); ?>">
              <a class="nav-link" href="gallery.php">Gallery</a>
            </li>
            <li class="nav-item <?php echo isPageActive('userfeed.php'); ?>">
              <a class="nav-link" href="userfeed.php">Newsfeed</a>
            </li>
          </ul>

          <!-- Right-aligned user profile and notifications -->
          <div class="d-flex align-items-center ms-auto">
            <ul class="navbar-nav ms-auto">
              <?php if ($isLoggedIn): ?>
                <!-- Notification Dropdown -->
                <li class="nav-item dropdown notification">
                  <a href="#" class="nav-link dropdown-toggle position-relative" id="alertsDropdown" data-bs-toggle="dropdown">
                    <i class="fa fa-bell text-light pt-2 m-0"></i>
                    <?php if ($unreadNotifications > 0): ?>
                      <span class="badge"><?php echo $unreadNotifications; ?></span>
                    <?php endif; ?>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end dropdown-menu-md shadow-sm " aria-labelledby="alertsDropdown" style="min-width: 350px; max-height: 300px; overflow-y: auto;">
                    <div class="dropdown-header bg-light fw-bold py-1 px-2 d-flex justify-content-between">
                      <span><?php echo $unreadNotifications > 0 ? "$unreadNotifications New Notifications" : "No New Notifications"; ?></span>
                      <?php if ($unreadNotifications > 0): ?>
                        <a href="#" class="text-muted" id="mark-all-read">Mark all as read</a>
                      <?php endif; ?>
                    </div>
                    <div class="list-group list-group-flush">
                      <!-- Notification items -->
                      <?php while ($notification = mysqli_fetch_assoc($notificationsResult)): ?>
                        <a href="#" class="list-group-item list-group-item-action py-2 px-2 <?php echo !$notification['is_read'] ? 'fw-bold' : 'text-muted'; ?>" style="font-size: 12px;">
                          <div class="d-flex align-items-center">
                            <div class="text-truncate"><?php echo htmlspecialchars($notification['message']); ?></div>
                          </div>
                        </a>
                      <?php endwhile; ?>
                    </div>
                    <div class="dropdown-footer text-center bg-light py-2">
                      <a href="#" id="view-all-notifications" class="text-muted">View all notifications</a>
                    </div>
                  </div>
                </li>

                <!-- Profile Dropdown with Icons -->
                <li class="nav-item dropdown profile ms-3">
                  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <span class="text-light"><?php echo htmlspecialchars($username); ?></span>
                    <!-- Ensure the image has equal width and height, and use rounded-circle class for Bootstrap -->
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                  </a>

                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile-user.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="user_blogs.php"><i class="fas fa-pen me-2"></i>My Blogs</a></li>
                    <li><a class="dropdown-item" href="archived_blogs.php"><i class="fas fa-archive me-2"></i>Archive</a></li>
                    <li>
                      <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="../Backend/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                  </ul>
                </li>
              <?php else: ?>
                <!-- Login/Signup Links -->
                <li class="nav-item ">
                  <a class="nav-link  text-light  shadow-sm  py-2" href="Login.php">Login</a>
                </li>
                <li class="nav-item ">
                  <a class="nav-link  text-light  shadow-sm py-2" href="Signup.php">Signup</a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </div>
</header>

<!-- Ensure FontAwesome is linked in your project -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- JS to ensure proper notification dropdown functionality -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const viewAllNotificationsLink = document.getElementById('view-all-notifications');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const markAllReadLink = document.getElementById('mark-all-read');

    // Handle "View all notifications" click
    viewAllNotificationsLink.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Prevent the dropdown from closing
      dropdownMenu.style.maxHeight = 'none'; // Remove the height restriction
      dropdownMenu.style.overflowY = 'visible'; // Allow full view of content
      viewAllNotificationsLink.style.display = 'none'; // Hide the "View all notifications" link
    });

    // Keep the dropdown open when clicking inside it
    dropdownMenu.addEventListener('click', function(e) {
      e.stopPropagation(); // Prevent closing the dropdown when clicking inside it
    });

    // Handle individual notification click
    document.querySelectorAll('.list-group-item').forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent the dropdown from closing
        const notificationId = this.getAttribute('data-id');

        fetch('addons/mark_as_read.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'notification_id=' + notificationId
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              this.classList.remove('fw-bold'); // Remove bold to indicate it's read
              this.classList.add('text-muted'); // Add muted class to mark it as read
              const dotIcon = this.querySelector('.fa-circle');
              if (dotIcon) {
                dotIcon.classList.remove('fa-circle', 'text-primary'); // Remove the unread dot
              }

              // Update the notification count
              let countElement = document.querySelector('.badge');
              let count = parseInt(countElement.textContent);
              if (count > 1) {
                countElement.textContent = count - 1;
              } else {
                countElement.remove(); // Remove the badge if no unread notifications are left
                markAllReadLink.style.display = 'none'; // Hide "Mark all as read" link
              }
            }
          });
      });
    });

    // Handle "Mark all as read" click
    markAllReadLink.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Prevent the dropdown from closing

      fetch('addons/mark_all_as_read.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: ''
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            document.querySelectorAll('.list-group-item').forEach(item => {
              item.classList.remove('fw-bold'); // Remove bold to indicate they are read
              item.classList.add('text-muted'); // Add muted to all notifications
              const dotIcon = item.querySelector('.fa-circle');
              if (dotIcon) {
                dotIcon.classList.remove('fa-circle', 'text-primary'); // Remove all unread dots
              }
            });
            document.querySelector('.badge').remove(); // Remove the unread notifications badge
            markAllReadLink.style.display = 'none'; // Hide "Mark all as read" link
          }
        });
    });
  });
</script>