<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// Use a prepared statement for security, even if not strictly necessary for session data
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE username = ? AND role = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result1 = $stmt->get_result();
$userData = $result1->fetch_assoc();

$count = $result1->num_rows;

if ($count == 0 || ($role !== 'superadmin' && $role !== 'admin')) {
    header("Location: Backend/Logout.php");
    exit();
}

$profilePicture = $userData['profile_picture'] ?? 'img/avatars/default_avatar.jpg'; // Fallback to a default avatar if not set
?>

<div class="navbar-collapse collapse">
    <ul class="navbar-nav navbar-align">
        <li class="nav-item dropdown">
            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                <i class="align-middle" data-feather="settings"></i>
            </a>

            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                <!-- Use Bootstrap 'rounded-circle' to make the image a circle, and 'me-2' for margin-right spacing -->
                <img src="<?php echo $profilePicture; ?>" 
                     class="avatar img-fluid rounded-circle me-2" 
                     alt="User Avatar" 
                     style="width: 40px; height: 40px;" /> 
                <span class="text-dark"><?php echo $username; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="pages-profile.php"><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                <a class="dropdown-item" href="../Backend/Logout.php">Log out</a>
            </div>
        </li>
    </ul>
</div>
</nav>
