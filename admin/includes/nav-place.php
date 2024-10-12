<?php
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$username || !$role) {
	header("Location: ../../../Login.php");
	exit();
}

// Use a prepared statement for security, even if not strictly necessary for session data
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result1 = $stmt->get_result();
$count = $result1->num_rows;

if ($count == 0 || ($role !== 'superadmin' && $role !== 'admin')) {
	header("Location: Backend/Logout.php");
	exit();
}
?>
