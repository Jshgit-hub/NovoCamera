<?php
session_start();
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    echo "Entered Password: $password<br>";  // Show the plain-text password entered

    // Fetch the user with the provided username and check if they are an admin
    $query = "SELECT * FROM users WHERE username = ? AND (role = 'admin' OR role = 'superadmin')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Debugging: Show the stored hash for the user
        echo "Stored Hash: " . $user['Password'] . "<br>";

        // Verify the password (Ensure the field name is correct)
        if (password_verify($password, $user['Password'])) {
            echo "Password verified!"; // Debugging statement

            // Session setup
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['Muni_ID'] = $user['Muni_ID']; // Assuming each admin is linked to a municipality

            // Redirect to the admin dashboard
             header("Location: ../../admin/admin_dashboard.php");
            exit();
        } else {    
            echo "Invalid password."; // Debugging statement
            $_SESSION['status'] = "Invalid password.";
            $_SESSION['status_code'] = "danger";
        }
    } else {
        echo "Invalid username or not authorized."; // Debugging statement
        $_SESSION['status'] = "Invalid username or not authorized.";
        $_SESSION['status_code'] = "danger";
    }

    // Redirect back to the login page with an error message
    header("Location: ../../admin/admin_login.php");
    exit();
}

