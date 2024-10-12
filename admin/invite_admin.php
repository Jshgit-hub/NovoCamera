<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/vendor/autoload.php';
include '../connection/connection.php';

// Retrieve any message stored in the session
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Fetch the list of municipalities
$municipalities = [];
$sql = "SELECT Muni_ID, MuniName FROM municipalities";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }
}

// Fetch the list of pending invitations (filtered by invitation status and incomplete profile)
$invitations = [];
$invitation_sql = "SELECT user_id, Email, Date_Created, invitation_status FROM users WHERE invitation_status = 'pending' AND Fullname = ''";
$invitation_result = $conn->query($invitation_sql);

if ($invitation_result && $invitation_result->num_rows > 0) {
    while ($row = $invitation_result->fetch_assoc()) {
        $invitations[] = $row;
    }
}

// Function to generate a random username based on email
function generateRandomUsername($email)
{
    $username = explode('@', $email)[0];
    $randomNumber = rand(1000, 9999);
    return $username . $randomNumber;
}

// Function to generate a random password
function generateRandomPassword($length = 8)
{
    return bin2hex(random_bytes($length / 2)); // Generates a random password
}

// Function to invite an admin
function inviteAdmin($email, $role = 'admin', $Muni_ID = null)
{
    global $conn;

    $username = generateRandomUsername($email);
    $password = generateRandomPassword();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));
    $expiry = date("Y-m-d H:i:s", strtotime('+24 hours'));
    $date_created = date("Y-m-d H:i:s");

    // Insert the user into the users table with incomplete profile and pending status
    $query = "INSERT INTO users (username, Fullname, Email, Password, Date_Created, role, Muni_ID, reset_token, reset_expires, verification_code, profile_picture, invitation_status) 
              VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, '', '', 'pending')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $username, $email, $hashedPassword, $date_created, $role, $Muni_ID, $token, $expiry);
    $stmt->execute();

    // Send the invitation email
    $link = "http://192.168.100.16:8080/admin/setup.php?token=$token";
    sendInvitationEmail($email, $username, $password, $link);
}

// Function to send the invitation email using PHPMailer and SMTP
function sendInvitationEmail($email, $username, $password, $link)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'novocameratour@gmail.com'; // Your email
        $mail->Password = 'wacp wjfx esew sopp'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('novocameratour@gmail.com', 'NovoCamera');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Admin Account Details';
        $mail->Body    = "
        <p>Hello,</p>
        <p>You have been invited to join as an admin. Below are your temporary login credentials:</p>
        <p><b>Username:</b> $username<br>
        <b>Password:</b> $password</p>
        <p>Please click the link below to complete your profile and activate your account:</p>
        <p><a href='$link'>$link</a></p>
        <p>Thank you!</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invite'])) {
    $email = $_POST['email'];
    $Muni_ID = $_POST['Muni_ID'];

    inviteAdmin($email, 'admin', $Muni_ID);

    $_SESSION['message'] = "Invitation sent to $email!";
    header("Location: invite_admin.php");
    exit();
}

// Handle cancel invitation
if (isset($_POST['cancel'])) {
    $user_id = $_POST['user_id'];
    
    // Delete the user from the users table
    $delete_query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $_SESSION['message'] = "Invitation cancelled and admin removed.";
    header("Location: invite_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Invite Admin</title>
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
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 text-light">Invite a New Municipal Admin</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="invite_admin.php">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="Muni_ID" class="form-label">Assign Municipality</label>
                                    <select class="form-control" id="Muni_ID" name="Muni_ID" required>
                                        <option value="">Select Municipality</option>
                                        <?php foreach ($municipalities as $municipality): ?>
                                            <option value="<?php echo $municipality['Muni_ID']; ?>">
                                                <?php echo $municipality['MuniName']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="invite" class="btn btn-primary">Send Invitation</button>
                            </form>
                        </div>
                    </div>

                    <!-- Display pending invitations -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0 text-light">Pending Invitations</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($invitations)): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Date Created</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invitations as $invitation): ?>
                                            <tr>
                                                <td><?php echo $invitation['Email']; ?></td>
                                                <td><?php echo $invitation['Date_Created']; ?></td>
                                                <td><?php echo $invitation['invitation_status']; ?></td>
                                                <td>
                                                    <form method="POST" action="invite_admin.php">
                                                        <input type="hidden" name="user_id" value="<?php echo $invitation['user_id']; ?>">
                                                        <button type="submit" name="cancel" class="btn btn-danger btn-sm">Cancel</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No pending invitations.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>

</html>
