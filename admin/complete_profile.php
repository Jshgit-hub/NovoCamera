<?php
session_start();
include '../connection/connection.php';

if (!isset($_GET['token'])) {
    $_SESSION['status'] = "Invalid token.";
    $_SESSION['status_code'] = "danger";
    header('Location: error.php');
    exit();
}

$token = $_GET['token'];

// Fetch user details using the token
$query = "SELECT * FROM users WHERE reset_token = ? AND invitation_status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['status'] = "Invalid or expired token.";
    $_SESSION['status_code'] = "danger";
    header('Location: error.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Fname = $_POST['Fname'];
    $Lname = $_POST['Lname'];
    $username = $_POST['username'];
    $description = $_POST['Description'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    // Update user profile
    $updateQuery = "UPDATE users SET Fullname = ?, username = ?, Password = ?, Description = ?, invitation_status = 'completed' WHERE reset_token = ?";
    $stmt = $conn->prepare($updateQuery);
    $fullname = $Fname . ' ' . $Lname;
    $stmt->bind_param("sssss", $fullname, $username, $password, $description, $token);
    
    if ($stmt->execute()) {
        $_SESSION['status'] = "Profile completed successfully. You can now log in.";
        $_SESSION['status_code'] = "success";
        header('Location: admin_login.php');
        exit();
    } else {
        $_SESSION['status'] = "Failed to complete your profile. Please try again.";
        $_SESSION['status_code'] = "danger";
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - Multi-Step Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">

    <?php if (isset($_SESSION['status']) && isset($_SESSION['status_code'])): ?>
        <div class="alert alert-<?php echo $_SESSION['status_code']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['status']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['status'], $_SESSION['status_code']); ?>
    <?php endif; ?>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="signup-container" id="signupContainer" style="width: 600px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div class="signup-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Complete Your Profile</h2>
                <p style="color: #666;">Please fill in the details to finish setting up your account.</p>
            </div>

            <!-- Step Progress Bar -->
            <div class="step-progress mb-4" style="display: flex; justify-content: space-between; position: relative; margin-bottom: 1.5rem;">
                <div class="progress-bar" style="position: absolute; top: 50%; left: 0; width: 100%; height: 4px; background-color: #ddd; transform: translateY(-50%); z-index: 0;"></div>
                <div class="progress-step progress-step-active" id="stepProgress1" style="width: 50%; text-align: center; position: relative; z-index: 1;">
                    <div style="content: ''; display: block; margin: 0 auto; width: 20px; height: 20px; background-color: #ddd; border-radius: 50%; border: 3px solid white;"></div>
                    <span style="display: block; margin-top: 5px; font-size: 14px; color: #379777; font-weight: bold;">Step 1</span>
                </div>
                <div class="progress-step" id="stepProgress2" style="width: 50%; text-align: center; position: relative; z-index: 1;">
                    <div style="content: ''; display: block; margin: 0 auto; width: 20px; height: 20px; background-color: #ddd; border-radius: 50%; border: 3px solid white;"></div>
                    <span style="display: block; margin-top: 5px; font-size: 14px; color: #666;">Step 2</span>
                </div>
            </div>

            <form action="" method="post" id="signupForm">
                <!-- Step 1 -->
                <div class="step-form active" id="step1">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="Fname" id="Fname" placeholder="First Name" required style="border-radius: 10px;" onkeypress="return /^[a-zA-Z\s]*$/.test(event.key)">
                        <small class="text-danger" id="nameError" style="display:none;">Name should only contain letters.</small>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="Lname" id="Lname" placeholder="Last Name" required style="border-radius: 10px;" onkeypress="return /^[a-zA-Z\s]*$/.test(event.key)">
                        <small class="text-danger" id="lnameError" style="display:none;">Name should only contain letters.</small>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="username" id="username" placeholder="Username" required style="border-radius: 10px;" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control form-control-lg bg-light fs-6" name="Description" id="Description" placeholder="Tell us about yourself (optional)" rows="3" style="border-radius: 10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-lg w-100 fs-6" id="next1" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; transition: background-color 0.3s ease; padding: 10px; font-size: 16px;">Next</button>
                    </div>
                    <div class="text-center">
                        <small>Already have an account? <a href="Login.php" style="color: #379777; text-decoration: none;">Login</a></small>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step-form" id="step2" style="display:none;">
                    <div class="mb-3">
                        <input type="email" class="form-control form-control-lg bg-light fs-6" name="email" id="email" placeholder="Email address" required style="border-radius: 10px;" value="<?php echo htmlspecialchars($user['Email']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control form-control-lg bg-light fs-6" name="password" id="password" placeholder="Password" required style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control form-control-lg bg-light fs-6" name="conpassword" id="conpassword" placeholder="Confirm Password" required style="border-radius: 10px;">
                        <small class="text-danger" id="passwordError" style="display:none;">Passwords do not match.</small>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required>
                            <label for="termsCheck" class="form-check-label text-secondary"><small>I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a></small></label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-lg fs-6" id="back1" style="background-color: #f1f1f1; color: #666; border: none; border-radius: 10px; transition: background-color 0.3s ease; padding: 10px;">Back</button>
                        <button type="submit" class="btn btn-lg w-100 fs-6" id="submitBtn" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; transition: background-color 0.3s ease; padding: 10px;">Complete Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Terms and Conditions Content -->
                    <!-- (Keep your Terms and Conditions content here) -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const next1 = document.getElementById('next1');
            const back1 = document.getElementById('back1');
            const passwordInput = document.getElementById('password');
            const conpasswordInput = document.getElementById('conpassword');
            const passwordError = document.getElementById('passwordError');
            const nameError = document.getElementById('nameError');
            const lnameError = document.getElementById('lnameError');

            next1.addEventListener('click', function() {
                if (validateStep1()) {
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2').style.display = 'block';
                    updateProgressBar('stepProgress1', 'stepProgress2');
                }
            });

            back1.addEventListener('click', function() {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step1').style.display = 'block';
                updateProgressBar('stepProgress2', 'stepProgress1', false);
            });

            function validateStep1() {
                const Fname = document.getElementById('Fname').value.trim();
                const Lname = document.getElementById('Lname').value.trim();

                let valid = true;

                if (!/^[a-zA-Z\s]+$/.test(Fname)) {
                    nameError.style.display = 'block';
                    valid = false;
                } else {
                    nameError.style.display = 'none';
                }

                if (!/^[a-zA-Z\s]+$/.test(Lname)) {
                    lnameError.style.display = 'block';
                    valid = false;
                } else {
                    lnameError.style.display = 'none';
                }

                return valid;
            }

            document.getElementById('signupForm').addEventListener('submit', function(e) {
                if (passwordInput.value !== conpasswordInput.value) {
                    passwordError.style.display = 'block';
                    e.preventDefault();
                } else {
                    passwordError.style.display = 'none';
                }
            });

            function updateProgressBar(prevStep, nextStep, forward = true) {
                document.getElementById(prevStep).querySelector('span').style.color = forward ? '#666' : '#379777';
                document.getElementById(nextStep).querySelector('span').style.color = forward ? '#379777' : '#666';
                document.getElementById(nextStep).querySelector('span').style.fontWeight = forward ? 'bold' : 'normal';
                document.getElementById(prevStep).classList.remove('progress-step-active');
                document.getElementById(nextStep).classList.add('progress-step-active');
            }
        });
    </script>
</body>

</html>
