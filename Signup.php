<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Multi-Step Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-progress {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 1.5rem;
        }

        .progress-bar {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: #ddd;
            transform: translateY(-50%);
            z-index: 0;
            transition: width 0.5s ease-in-out;
        }

        .progress-bar-fill {
            background-color: #379777;
            width: 0;
            height: 100%;
            transition: width 0.5s ease-in-out;
        }

        .progress-step {
            width: 50%;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .progress-step-active .step-circle {
            background-color: #379777;
            color: white;
        }

        .progress-step-completed .step-circle {
            background-color: #379777;
            color: white;
            content: '\2713';
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: white;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .progress-step span {
            margin-top: 5px;
            font-size: 14px;
            display: block;
            font-weight: normal;
            color: #666;
            transition: color 0.3s ease, font-weight 0.3s ease;
        }

        .progress-step-active span {
            font-weight: bold;
            color: #379777;
        }

        .spinner-border {
            display: none;
        }

        .loading .spinner-border {
            display: inline-block;
        }

        .loading .submit-text {
            display: none;
        }
    </style>
</head>

<body style="background-color: #ececec; font-family: 'Poppins', sans-serif;">

    <!-- Display status message -->
    <?php if (isset($_SESSION['status']) && isset($_SESSION['status_code'])): ?>
        <div class="alert alert-<?php echo $_SESSION['status_code']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['status']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['status'], $_SESSION['status_code']); ?>
    <?php endif; ?>

    <!-- Display form validation errors -->
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 w-100">
        <div class="signup-container" id="signupContainer" style="width: 600px; margin: auto; padding: 2rem; background: #fff; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div class="signup-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-weight: 500; margin-bottom: 0.5rem;">Create Account</h2>
                <p style="color: #666;">We are excited to have you join us.</p>
            </div>

            <!-- Step Progress Bar -->
            <div class="step-progress mb-4">
                <div class="progress-bar">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
                </div>
                <div class="progress-step progress-step-active" id="stepProgress1">
                    <div class="step-circle">1</div>
                    <span>Step 1</span>
                </div>
                <div class="progress-step" id="stepProgress2">
                    <div class="step-circle">2</div>
                    <span>Step 2</span>
                </div>
            </div>

            <form action="Backend/Signup_Config.php" method="post" id="signupForm">
                <!-- Step 1 -->
                <div class="step-form active" id="step1">
                    <div class="mb-3">
                        <label for="Fname" class="form-label">First Name</label>
                        <input type="text" class="form-control form-control-lg bg-light fs-6 <?php echo isset($_SESSION['errors']['fullname']) ? 'is-invalid' : ''; ?>" name="Fname" id="Fname" placeholder="First Name" required style="border-radius: 10px;" value="<?php echo isset($_SESSION['old_input']['Fname']) ? htmlspecialchars($_SESSION['old_input']['Fname']) : ''; ?>">
                        <small class="text-danger"><?php echo isset($_SESSION['errors']['fullname']) ? $_SESSION['errors']['fullname'] : ''; ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="Lname" class="form-label">Last Name</label>
                        <input type="text" class="form-control form-control-lg bg-light fs-6 <?php echo isset($_SESSION['errors']['lastname']) ? 'is-invalid' : ''; ?>" name="Lname" id="Lname" placeholder="Last Name" required style="border-radius: 10px;" value="<?php echo isset($_SESSION['old_input']['Lname']) ? htmlspecialchars($_SESSION['old_input']['Lname']) : ''; ?>">
                        <small class="text-danger"><?php echo isset($_SESSION['errors']['lastname']) ? $_SESSION['errors']['lastname'] : ''; ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control form-control-lg bg-light fs-6 <?php echo isset($_SESSION['errors']['username']) ? 'is-invalid' : ''; ?>" name="username" id="username" placeholder="Username" required style="border-radius: 10px;" value="<?php echo isset($_SESSION['old_input']['username']) ? htmlspecialchars($_SESSION['old_input']['username']) : ''; ?>">
                        <small class="text-danger"><?php echo isset($_SESSION['errors']['username']) ? $_SESSION['errors']['username'] : ''; ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="Description" class="form-label">Tell us about yourself (optional)</label>
                        <textarea class="form-control form-control-lg bg-light fs-6" name="Description" id="Description" placeholder="Tell us about yourself" rows="3" style="border-radius: 10px;"><?php echo isset($_SESSION['old_input']['Description']) ? htmlspecialchars($_SESSION['old_input']['Description']) : ''; ?></textarea>
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
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-lg bg-light fs-6 <?php echo isset($_SESSION['errors']['email']) ? 'is-invalid' : ''; ?>" name="email" id="email" placeholder="Email address" required style="border-radius: 10px;" value="<?php echo isset($_SESSION['old_input']['email']) ? htmlspecialchars($_SESSION['old_input']['email']) : ''; ?>">
                        <small class="text-danger"><?php echo isset($_SESSION['errors']['email']) ? $_SESSION['errors']['email'] : ''; ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg bg-light fs-6" name="password" id="password" placeholder="Password" required style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label for="conpassword" class="form-label">Confirm Password</label>
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
                        <button type="submit" name="register" class="btn btn-lg w-100 fs-6" id="submitBtn" style="background-color: #379777; color: #fff; border: none; border-radius: 10px; transition: background-color 0.3s ease; padding: 10px;">
                            <span class="submit-text">Sign Up</span>
                            <div class="spinner-border spinner-border-sm text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const next1 = document.getElementById('next1');
            const back1 = document.getElementById('back1');
            const submitBtn = document.getElementById('submitBtn');
            const progressBarFill = document.getElementById('progressBarFill');
            const passwordInput = document.getElementById('password');
            const conpasswordInput = document.getElementById('conpassword');
            const passwordError = document.getElementById('passwordError');
            let isSubmitting = false; // Prevent multiple submits

            // Validate Step 1 before allowing to go to Step 2
            next1.addEventListener('click', function() {
                if (validateStep1()) {
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2').style.display = 'block';
                    updateProgressBar('stepProgress1', 'stepProgress2', true);
                }
            });

            back1.addEventListener('click', function() {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step1').style.display = 'block';
                updateProgressBar('stepProgress2', 'stepProgress1', false);
            });

            document.getElementById('signupForm').addEventListener('submit', function(e) {
                // Prevent multiple submissions
                if (isSubmitting) {
                    e.preventDefault();
                    return;
                }

                // Validate password matching
                if (passwordInput.value !== conpasswordInput.value) {
                    passwordError.style.display = 'block';
                    e.preventDefault();
                } else {
                    passwordError.style.display = 'none';
                    
                    // Add loading spinner to the submit button
                    submitBtn.classList.add('loading');
                    isSubmitting = true;
                }
            });

            function validateStep1() {
                const Fname = document.getElementById('Fname').value.trim();
                const Lname = document.getElementById('Lname').value.trim();
                const username = document.getElementById('username').value.trim();

                let valid = true;

                if (Fname === '') {
                    valid = false;
                    alert("First Name is required.");
                }
                if (Lname === '') {
                    valid = false;
                    alert("Last Name is required.");
                }
                if (username === '') {
                    valid = false;
                    alert("Username is required.");
                }

                return valid;
            }

            function updateProgressBar(prevStep, nextStep, forward = true) {
                document.getElementById(prevStep).classList.remove('progress-step-active');
                document.getElementById(prevStep).classList.add('progress-step-completed');
                document.getElementById(nextStep).classList.add('progress-step-active');

                if (forward) {
                    progressBarFill.style.width = "100%";
                } else {
                    progressBarFill.style.width = "50%";
                }
            }
        });
    </script>
</body>

</html>

<?php unset($_SESSION['old_input']); ?>
