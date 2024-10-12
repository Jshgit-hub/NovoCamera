<?php 
session_start();
include '../connection/connection.php'; // Include your database connection file

// Fetch user details from session or database
$user_id = $_SESSION['user_id'];
$query = "SELECT u.Username, u.Fullname, u.Email, u.Description, u.Role, u.profile_picture, m.MuniName 
          FROM users u 
          LEFT JOIN Municipalities m ON u.Muni_ID = m.Muni_ID 
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $profile_picture = $user['profile_picture'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file = $_FILES['profile_picture'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = 'profile_' . $user_id . '.' . $ext;
            $destination = '../assets/images/profile_pictures/' . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $profile_picture = $destination;
            }
        }
    }

    // Update user details in the database
    $query = "UPDATE users SET Username = ?, Fullname = ?, Email = ?, Description = ?, profile_picture = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $username, $fullname, $email, $description, $profile_picture, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        header('Location: pages-profile.php');
        exit();
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}

// Fallback for profile picture
if (empty($user['profile_picture'])) {
    $profile_picture = 'img/avatars/custom_avatar.png'; // Path to custom avatar
} else {
    $profile_picture = $user['profile_picture'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="img/icons/icon-48x48.png" />
	<link href="css/app.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

	<!-- Add custom styles for perfect circle -->
	<style>
		.profile-image {
			width: 128px;
			height: 128px;
			object-fit: cover; /* Ensures the image covers the container fully while maintaining aspect ratio */
			border-radius: 50%; /* Makes the image a perfect circle */
			border: 3px solid #ddd; /* Optional: Adds a border around the image */
		}
	</style>
</head>

<body>
	<div class="wrapper">
		<?php include("includes/adminsidebar.php"); ?>

		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<?php include('includes/navbar-top.php'); ?>
			</nav>
			<main class="content">
				<div class="container-fluid p-0">

					<div class="mb-3">
						<h1 class="h3 d-inline align-middle">Profile</h1>
					</div>
					<div class="row">
						<div class="col-md-4 col-xl-3">
							<div class="card mb-3">
								<div class="card-header">
									<h5 class="card-title mb-0">Profile Details</h5>
								</div>
								<div class="card-body text-center">
									<img src="<?php echo $profile_picture; ?>" alt="<?php echo $user['Fullname']; ?>" class="profile-image mb-2" />
									<h5 class="card-title mb-0"><?php echo $user['Fullname']; ?></h5>
									<div class="text-muted mb-2"><?php echo ucfirst($user['Role']); ?></div>
								</div>
								<div class="card-body">
									<h5 class="h6 card-title">About</h5>
									<ul class="list-unstyled mb-0">
										<li class="mb-1"><span data-feather="home" class="feather-sm me-1"></span> Works at <a href="#"><?php echo $user['MuniName']; ?></a></li>
										<li class="mb-1"><span data-feather="mail" class="feather-sm me-1"></span> Email: <a href="mailto:<?php echo $user['Email']; ?>"><?php echo $user['Email']; ?></a></li>
									</ul>
								</div>
								<hr class="my-0" />
							</div>
						</div>

						<div class="col-md-8 col-xl-9">
							<div class="card">
								<div class="card-header">
									<h5 class="card-title mb-0">Edit Details</h5>
								</div>
								<div class="card-body">
									<?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
									<form action="pages-profile.php" method="POST" enctype="multipart/form-data">
										<div class="mb-3">
											<label class="form-label">Username</label>
											<input type="text" class="form-control" name="username" value="<?php echo $user['Username']; ?>" required>
										</div>
										<div class="mb-3">
											<label class="form-label">Full Name</label>
											<input type="text" class="form-control" name="fullname" value="<?php echo $user['Fullname']; ?>" required>
										</div>
										<div class="mb-3">
											<label class="form-label">Email</label>
											<input type="email" class="form-control" name="email" value="<?php echo $user['Email']; ?>" required>
										</div>
										<div class="mb-3">
											<label class="form-label">Description</label>
											<textarea class="form-control" name="description" rows="4"><?php echo $user['Description']; ?></textarea>
										</div>
										<div class="mb-3">
											<label class="form-label">Profile Picture</label>
											<input type="file" class="form-control" name="profile_picture" accept="image/*">
											<small class="form-text text-muted">Leave blank to keep the current picture.</small>
										</div>
										<button type="submit" class="btn btn-primary">Update Profile</button>
									</form>
								</div>
							</div>
						</div>
					</div>

				</div>
			</main>
		</div>
	</div>

	<script src="js/app.js"></script>

</body>

</html>
