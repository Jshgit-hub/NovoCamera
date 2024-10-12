<?php
// Start the session
session_start();

// Include the database connection file
include 'connection/connection.php';

$isLoggedIn = isset($_SESSION['Username']);

$user_id = $_SESSION['user_id'];

// Fetch user details from the `users` table
$query = "SELECT users.username, users.Fullname, users.Email, users.Description, users.Date_Created, users.role, municipalities.MuniName, users.profile_picture 
          FROM users 
          LEFT JOIN municipalities ON users.Muni_ID = municipalities.Muni_ID 
          WHERE users.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

// Fetch user's approved posts and photos from the `posts` table
$posts_query = "SELECT post.post_id, post.title, post.image_url, post.description1, post.created_at, users.username,
                COALESCE((SELECT COUNT(*) FROM likes WHERE likes.post_id = post.post_id), 0) AS likes_count,
                COALESCE((SELECT COUNT(*) FROM shares WHERE shares.post_id = post.post_id), 0) AS shares_count
                FROM post
                INNER JOIN users ON post.user_id = users.user_id
                WHERE post.user_id = ? AND post.status = 'approved'
                ORDER BY post.created_at DESC";
$stmt_posts = $conn->prepare($posts_query);
$stmt_posts->bind_param("i", $user_id);
$stmt_posts->execute();
$posts_result = $stmt_posts->get_result();

$photos = [];
while ($post = $posts_result->fetch_assoc()) {
    if (!empty($post['image_url'])) {
        $photos[] = $post['image_url'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - NovoCamera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-2.css">
    <style>
        /* Style for inactive tabs */
        .nav-link {
            color: #379777;
            /* Inactive tab text color */
        }

        /* Style for active tab */
        .nav-link.active {
            color: #fff;
            /* White text for active tab */
            background-color: #379777;
            /* Background color for active tab */
        }

        /* Hover effect for non-active tabs */
        .nav-link:hover {
            color: #fff;
            /* Text color on hover */
        }
    </style>


</head>


<body class="bg-light">

    <!-- Navbar -->
    <header class="header_area">
        <div class="main_menu">
            <?php include 'includes/navbar.php'; ?>
        </div>
    </header>

    <?php

    // Check for messages in the session
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        echo '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">';
        echo $message['text'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';

        // Remove the message from session after displaying it
        unset($_SESSION['message']);
    }
    ?>

    <!-- Your profile page content goes here -->


    <!-- Sidebar and Main Content -->
    <div class="d-flex flex-column flex-md-row">
        <!-- Sidebar -->
        <div class="col-md-3 text-white text-center pt-5 p-4 shadow-sm" style="min-height: 100vh;">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="User Avatar" class="rounded-circle mb-3 img-fluid shadow"
                style="width: 180px; height: 180px; object-fit: cover; border: 5px solid #fff;">
            <h4 class="mt-3 fw-bold text-dark" style="font-size: 1.5rem;"><?php echo htmlspecialchars($user['username']); ?></h4>
            <p class="mb-1 text-dark" style="font-size: 1.2rem;"><?php echo htmlspecialchars($user['Fullname']); ?></p>
            <p class="text-muted"><?php echo htmlspecialchars($user['Email']); ?></p>
            <button class="btn btn-success text-light fw-bold  mt-4 px-4 py-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                Edit Profile
            </button>
        </div>


        <!-- Main Content -->
        <div class="col-md-9 p-4 bg-white rounded-3 shadow-sm" style="min-height: 100vh;">

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs mb-3 bg-success">
                <li class="nav-item">
                    <a class="nav-link active fw-bold px-3" id="photos-tab" data-bs-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="true">Photos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold px-3" id="posts-tab" data-bs-toggle="tab" href="#my-posts" role="tab" aria-controls="posts" aria-selected="false">My Posts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold px-3" id="security-tab" data-bs-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">Security Settings</a>
                </li>
            </ul>

            <div class="tab-content flex-grow-1">
                <!-- Photos Section -->
                <div class="tab-pane fade show active" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                    <div class="row">
                        <?php if (count($photos) > 0): ?>
                            <?php foreach ($photos as $photo_url): ?>
                                <div class="col-md-4 mb-3">
                                    <a href="<?php echo htmlspecialchars($photo_url); ?>" target="_blank">
                                        <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Photo" class="img-fluid rounded shadow-sm">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No photos available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- My Posts Section -->
                <div class="tab-pane fade" id="my-posts" role="tabpanel" aria-labelledby="posts-tab">
                    <div id="posts-feed">
                        <?php
                        $posts_result->data_seek(0);
                        while ($post = $posts_result->fetch_assoc()):
                            $post_id = $post['post_id'];
                            $title = $post['title'];
                            $description_snippet = substr($post['description1'], 0, 100) . '...';
                            $likes_count = isset($post['likes_count']) ? $post['likes_count'] : 0;
                            $shares_count = isset($post['shares_count']) ? $post['shares_count'] : 0;
                            $profile_picture = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/profile-placeholder.png';
                            $post_image = $post['image_url'];
                            $created_at = date('F j, Y, g:i a', strtotime($post['created_at']));
                        ?>

                            <a href="user-post-details.php?post_id=<?php echo $post_id; ?>" class="text-decoration-none text-dark">
                                <div class="post-container card mb-4"
                                    style="border-radius: 12px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); overflow: hidden; cursor: pointer;"
                                    data-post-id="<?php echo $post_id; ?>">

                                    <!-- Post Header -->
                                    <div class="post-header d-flex align-items-center p-3" style="background-color: #f9fafb;">
                                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="User Avatar"
                                            class="rounded-circle me-3" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid #379777;">
                                        <div>
                                            <h6 class="m-0" style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($post['username']); ?></h6>
                                            <small class="text-muted">Posted on <?php echo $created_at; ?></small>
                                        </div>
                                    </div>

                                    <!-- Post Body -->
                                    <div class="post-body p-0" style="transition: all 0.3s ease;">
                                        <?php if (!empty($post_image)): ?>
                                            <div style="width: 100%; height: 350px; overflow: hidden;">
                                                <img src="<?php echo htmlspecialchars($post_image); ?>" class="img-fluid" alt="Post Image"
                                                    style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                            </div>
                                        <?php endif; ?>
                                        <div class="p-3">
                                            <h5 class="post-title" style="font-size: 1.25rem; color: #333; margin-bottom: 0.5rem; font-weight: 700;">
                                                <?php echo htmlspecialchars($title); ?>
                                            </h5>
                                            <p class="post-content" style="font-size: 1rem; color: #666;"><?php echo htmlspecialchars($description_snippet); ?></p>
                                        </div>
                                    </div>

                                    <!-- Post Actions -->
                                    <div class="post-actions d-flex justify-content-between align-items-center p-3"
                                        style="background-color: #f9fafb; border-top: 1px solid #eee;">
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-outline-success btn-sm me-3" style="border-radius: 50px;"
                                                onclick="event.preventDefault(); likePost(event, <?php echo $post_id; ?>, this)">
                                                <i class="fas fa-thumbs-up"></i> Like <span class="like-count"><?php echo $likes_count; ?></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm" style="border-radius: 50px;"
                                                onclick="event.preventDefault(); sharePost('<?php echo urlencode($post['title']); ?>', '<?php echo urlencode($post['image_url']); ?>', '<?php echo urlencode("http://yourwebsite.com/user-post-details.php?post_id={$post_id}"); ?>');">
                                                <i class="fas fa-share"></i> Share
                                            </button>
                                        </div>
                                        <small class="text-muted"><?php echo $shares_count; ?> Shares</small>
                                    </div>
                                </div>
                            </a>

                        <?php endwhile; ?>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-outline-success mt-4">Load More</button>
                    </div>
                </div>




                <!-- Security Settings Section -->
                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                    <form action="user-controller/change_password.php" method="POST">
                        <!-- Change Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-4">Change Password</button>
                    </form>
                </div>

                <!-- Security Questions -->

                <!-- Two-Factor Authentication -->

                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="user-controller/update_profile.php" method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <img id="profilePicturePreview" src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture Preview" class="rounded-circle img-fluid shadow" style="width: 120px; height: 120px;">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['Fullname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($user['Description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" onchange="previewProfilePicture(event)">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="vendors/owl-carousel/owl.carousel.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewProfilePicture(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profilePicturePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function likePost(event, postId, button) {
            event.stopPropagation(); // Prevent triggering the event on parent elements

            $.ajax({
                url: 'userfeed-action/like_post.php',
                type: 'POST',
                data: {
                    post_id: postId
                },
                success: function(response) {
                    var likeCountElement = $(button).find('.like-count');
                    var currentCount = parseInt(likeCountElement.text()) || 0;

                    if (response === 'liked') {
                        likeCountElement.text(currentCount + 1);
                        $(button).html('<i class="fas fa-heart"></i> <span class="like-count">' + (currentCount + 1) + '</span>');
                    } else if (response === 'unliked') {
                        likeCountElement.text(currentCount - 1);
                        $(button).html('<i class="fas fa-heart"></i> <span class="like-count">' + (currentCount - 1) + '</span>');
                    }
                },
                error: function() {
                    alert('An error occurred while processing your request.');
                }
            });
        }

        function sharePost(title, imageUrl, postUrl) {
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${postUrl}&quote=${title}`;
            const twitterUrl = `https://twitter.com/intent/tweet?text=${title}&url=${postUrl}`;
            const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${postUrl}`;

            const shareOptions = [{
                    platform: 'Facebook',
                    url: facebookUrl
                },
                {
                    platform: 'Twitter',
                    url: twitterUrl
                },
                {
                    platform: 'LinkedIn',
                    url: linkedinUrl
                }
            ];

            const shareLinks = shareOptions.map(option => {
                return `<a href="${option.url}" target="_blank" class="btn btn-primary me-2">${option.platform}</a>`;
            }).join('');

            const shareWindow = window.open('', 'Share', 'width=600,height=400');

            shareWindow.document.write(`
                <html>
                    <head>
                        <title>Share this post</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                text-align: center;
                                margin: 0;
                                padding: 0;
                                background-color: #f4f4f4;
                                display: flex;
                                flex-direction: column;
                                justify-content: center;
                                height: 100vh;
                                color: #333;
                            }
                            h3 {
                                margin-bottom: 20px;
                            }
                            .share-buttons {
                                display: flex;
                                justify-content: center;
                                gap: 15px;
                            }
                            .share-button {
                                padding: 12px 20px;
                                border: none;
                                border-radius: 5px;
                                color: #fff;
                                text-decoration: none;
                                font-size: 16px;
                                display: inline-block;
                                cursor: pointer;
                                transition: background-color 0.3s ease;
                            }
                            .facebook {
                                background-color: #4267B2;
                            }
                            .facebook:hover {
                                background-color: #365899;
                            }
                            .twitter {
                                background-color: #1DA1F2;
                            }
                            .twitter:hover {
                                background-color: #0d95e8;
                            }
                            .linkedin {
                                background-color: #0077B5;
                            }
                            .linkedin:hover {
                                background-color: #005582;
                            }
                        </style>
                    </head>
                    <body>
                        <h3>Share this post</h3>
                        <p>Choose a platform to share this post:</p>
                        <div class="share-buttons">
                            ${shareLinks}
                        </div>
                    </body>
                </html>
            `);

            shareWindow.document.close();
        }
    </script>
</body>

</html>

<?php
$stmt->close();
$stmt_posts->close();
$conn->close();
?>