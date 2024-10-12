<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - NovoCamEra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #f8d7da; font-family: 'Poppins', sans-serif;">

    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="error-container text-center" style="width: 500px; padding: 2rem; background: #fff; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h1 style="font-size: 72px; color: #dc3545;">Error</h1>
            <p class="lead text-danger">
                <?php
                if (isset($_SESSION['status'])) {
                    echo $_SESSION['status'];
                    unset($_SESSION['status']);
                } else {
                    echo "An unexpected error occurred. Please try again later.";
                }
                ?>
            </p>
            <a href="../index.php" class="btn btn-primary">Return to Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
