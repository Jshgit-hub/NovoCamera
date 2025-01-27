<?php
session_start();
include 'connection/connection.php';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);
$profilePicture = 'assets/images/profile-placeholder.png'; // Default profile picture
$municipalities = [];
$sql = "SELECT Muni_ID, MuniName FROM municipalities";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $municipalities[] = $row;
    }
}

$top_places = [];
$query = "SELECT PlaceName, PlacePicture, PlaceLocation FROM places WHERE top_place = 1 LIMIT 5";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $top_places[] = $row;
    }
}


$top_blogs = [];
$query = "SELECT title, image_url, location_name, blog_id FROM blogs WHERE is_top_blog = 1 AND status = 'published' LIMIT 5";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $top_blogs[] = $row;
    }
}

if ($isLoggedIn) {
    $username = $_SESSION['username'];
    // Query to fetch profile picture and user role
    $query = "SELECT profile_picture, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['profile_picture'])) {
            $profilePicture = $row['profile_picture']; // Use the user's profile picture
        }
        // Store user role in session
        $_SESSION['role'] = $row['role'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Explore Nueva Ecija - User Feed</title>
    <link rel="icon" href="img/Fevicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="vendors/owl-carousel/owl.theme.default.min.css">
    <link rel="stylesheet" href="vendors/owl-carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="css/style-2.css">

    <script src="https://cdn.tiny.cloud/1/oy68l2h0sa4jfdfo22mf4yxtsgy5rv538g9tdbgpzqerndjo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>



    <style>
        .tox {
            width: 100% !important;
            /* Ensure the editor is responsive */
            height: 300px !important;
            /* Adjust to appropriate height */
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .container-fluid {
            max-width: 1000px;
            margin: auto;
            padding-top: 20px;
        }

        .main-content {
            display: flex;
            justify-content: space-between;
        }


        .sidebar {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 30%;
        }



        .feed {
            width: 65%;
        }

        .profile-box,
        .share-post,
        .post-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .profile-box {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .profile-box img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .profile-box h5 {
            margin: 0;
            font-size: 1.25rem;
        }

        .profile-box small {
            display: block;
            color: #888;
            font-size: 0.875rem;
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .post-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-header h6 {
            font-size: 1rem;
            margin: 0;
        }

        .post-header small {
            display: block;
            color: #888;
            font-size: 0.875rem;
        }

        .post-image {
            width: 100%;
            max-height: 400px;
            border-radius: 10px;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .no-more-posts {
            text-align: center;
            padding: 20px;
            color: #999;
            font-weight: bold;
            display: none;
        }

        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
                align-items: center;
            }

            .sidebar,
            .feed {
                width: 100%;
            }

            .profile-box img {
                width: 50px;
                height: 50px;
            }

            .post-content {
                font-size: 0.9rem;
            }
        }

        .dimmed-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            z-index: 999;
        }

        .highlight {
            position: relative;
            z-index: 1000;
            animation: pulse 1.5s infinite;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(255, 215, 0, 1), 0 0 20px rgba(255, 215, 0, 0.8);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
            }

            50% {
                box-shadow: 0 0 30px rgba(255, 215, 0, 1);
            }

            100% {
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
            }
        }

        .tutorial-modal {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1001;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 350px;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .tutorial-btn {
            cursor: pointer;
            border: none;
            background-color: #2d6a4f;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .tutorial-btn:hover {
            background-color: #379777;
        }

        .tutorial-pointer {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-top: 15px solid #fff;
        }
    </style>
</head>

<body>


    <!-- Header -->
    <header class="header_area">
        <div class="main_menu">
            <?php include 'includes/navbar.php'; ?>
        </div>
    </header>




    <div class="container mt-5">
        <!-- Bootstrap Alert for Blog Submission -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong><?php echo htmlspecialchars($message); ?></strong> Please wait for the admin to approve your post.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <!-- Header -->

        <!-- Overlay for not logged in users -->

        <!-- Login/Register Modal -->
        <?php if (!$isLoggedIn): ?>
            <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="loginModalLabel">Welcome</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You need to log in or register to access this feature.</p>
                            <button class="btn btn-primary" onclick="window.location.href='login.php'">Log In</button>
                            <button class="btn btn-secondary" onclick="window.location.href='register.php'">Register</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var myModal = new bootstrap.Modal(document.getElementById('loginModal'), {
                        keyboard: false,
                        backdrop: 'static'
                    });
                    myModal.show();
                });
            </script>
        <?php endif; ?>

    </div>
    <?php if ($isLoggedIn): ?>
        <div class="container-fluid">
            <div class="main-content">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <!-- Profile Box -->
                    <div class="profile-box">
                        <a href="profile-user.php" style="text-decoration: none; color: inherit;">
                            <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="User Avatar" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                            <div>
                                <h5><?php echo $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></h5>
                                <?php if ($isLoggedIn): ?>
                                    <small>@<?php echo htmlspecialchars($_SESSION['username']); ?></small>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>

                </aside>


                <!-- Main Content / Feed -->
                <div class="feed">
                    <!-- Profile Box -->


                    <!-- Share Post -->
                    <?php if ($isLoggedIn && $_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin'): ?>
                        <div class="action-buttons mb-3">
                            <!-- Create Blog Button (only visible for non-admin and non-superadmin) -->
                            <a href="create_blog_form.php" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus"></i> Create Blog
                            </a>
                            <!-- Share Post Button -->
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#postModal">
                                <i class="fas fa-edit"></i> Share Post
                            </button>
                        </div>
                    <?php elseif ($isLoggedIn && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')): ?>
                        <div class="action-buttons mb-3">
                            <!-- Share Post Button (admins and superadmins can only share posts) -->
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#postModal">
                                <i class="fas fa-edit"></i> Share Post
                            </button>
                        </div>
                    <?php endif; ?>




                    <!-- User Posts -->
                    <div id="userPosts">
                        <!-- Post Card -->
                    </div>

                    <!-- No More Posts Message -->
                    <div id="noMorePosts" class="no-more-posts">
                        No more posts to load.
                    </div>
                </div>
            </div>
        </div>


        <!-- Custom Tutorial Modals -->
        <!-- Dimmed Background -->
        <div id="dimmedBackground" class="dimmed-background" style="display:none;"></div>

        <!-- Custom Tutorial Modals -->
        <div id="tutorialStep1" class="tutorial-modal" style="display:none;">
            <p>Welcome! Let’s start by learning how to create a post.</p>
            <button class="tutorial-btn" id="nextStep1">Next</button>
        </div>

        <div id="tutorialStep2" class="tutorial-modal" style="display:none;">
            <p>Click the <strong>"Share Post"</strong> button to create a new post!</p>
            <button class="tutorial-btn" id="nextStep2">Next</button>
        </div>

        <?php if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin'): ?>
            <div id="tutorialStep3" class="tutorial-modal" style="display:none;">
                <p>To create a blog, use the <strong>"Create Blog"</strong> button!</p>
                <button class="tutorial-btn" id="finishTutorial">Finish</button>
            </div>
        <?php endif; ?>


        <!-- Modal for Creating a Post -->
        <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border-radius: 10px; overflow: hidden; font-family: 'Lora', serif;">
                    <div class="modal-header" style="background-color: #2d6a4f; color: white; border-bottom: none;">
                        <h5 class="modal-title mx-auto" id="postModalLabel" style="font-weight: 600;">Create a Post</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        <form id="createPostForm" action="Backend/add-post-user-config.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="postImage" class="form-label">Upload Image</label>
                                <input type="file" class="form-control" id="postImage" name="postImage" accept="image/*" onchange="previewImage(event)">
                                <img id="imagePreview" class="img-fluid mt-3" style="display: none; border-radius: 5px;">
                            </div>
                            <div class="mb-3">
                                <label for="postTitle" class="form-label">Post Title</label>
                                <input type="text" class="form-control" id="postTitle" name="postTitle" placeholder="Enter the title of your post">
                            </div>

                            <div class="mb-3">
                                <label for="postDescription11" class="form-label">Description</label>
                                <textarea id="postDescription1" name="description1" rows="4" class="form-control"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="locationSelect" class="form-label">Select Location</label>
                                <select id="locationSelect" name="Muni_ID" class="form-select">
                                    <option value="" disabled selected>Select a location</option>
                                    <?php
                                    // Fetching municipalities from the database
                                    $query = "SELECT Muni_ID, MuniName FROM municipalities";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['Muni_ID'] . "'>" . $row['MuniName'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="placeType" class="form-label">Place Type</label>
                                <select class="form-select" id="placeType" name="placeType">
                                    <option value="" disabled selected>Select a type of place</option>
                                    <option value="River">River</option>
                                    <option value="Church">Church</option>
                                    <option value="Mountain">Mountain</option>
                                    <option value="Park">Park</option>
                                    <option value="Lake">Lake</option>
                                    <option value="Historical Site">Historical Site</option>
                                    <option value="Museum">Museum</option>
                                    <option value="Bridge">Bridge</option>
                                    <option value="Waterfall">Waterfall</option>
                                    <option value="Temple">Temple</option>
                                </select>
                            </div>

                            <div class="text-center">
                                <button type="submit" name="submit" class="btn btn-primary w-100" style="background-color: #2d6a4f; border-radius: 5px; padding: 10px 0;">Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Check if the tutorial has already been shown
            if (!sessionStorage.getItem('tutorialShown')) {
                // Start by showing the first tutorial modal and dimming the background
                $('#dimmedBackground').fadeIn();
                $('#tutorialStep1').fadeIn();

                // Set the sessionStorage flag so that the tutorial is not shown again in this session
                sessionStorage.setItem('tutorialShown', 'true');
            }

            // Step 1 -> Step 2
            $('#nextStep1').click(function() {
                $('#tutorialStep1').fadeOut(function() {
                    $('#sharePostButton').addClass('highlight'); // Highlight the Share Post button
                    $('#tutorialStep2').fadeIn();
                });
            });

            // Step 2 -> Step 3 (if not admin)
            $('#nextStep2').click(function() {
                $('#sharePostButton').removeClass('highlight');
                $('#tutorialStep2').fadeOut(function() {
                    <?php if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin'): ?>
                        $('#createBlogButton').addClass('highlight');
                        $('#tutorialStep3').fadeIn();
                    <?php else: ?>
                        $('#dimmedBackground').fadeOut(); // End the tutorial for admin/superadmin users
                    <?php endif; ?>
                });
            });

            // Finish the tutorial
            $('#finishTutorial').click(function() {
                $('#createBlogButton').removeClass('highlight');
                $('#tutorialStep3').fadeOut(function() {
                    $('#dimmedBackground').fadeOut(); // End the tutorial for non-admin users
                });
            });
        });
    </script>



    <script>
        function previewImage(event) {
            const input = event.target;
            const reader = new FileReader();
            reader.onload = function() {
                const imagePreview = document.getElementById('imagePreview');
                imagePreview.src = reader.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
        $(document).ready(function() {
            let offset = 0;
            const limit = 5;
            let loading = false;
            let noMorePosts = false;

            function loadPosts() {
                if (!loading && !noMorePosts) {
                    loading = true;
                    $.post("ajax_content/fetch_posts.php", {
                        offset: offset
                    }, function(data) {
                        if (data.trim().length > 0) {
                            $('#userPosts').append(data);
                            offset += limit;
                        } else {
                            noMorePosts = true;
                            // Unbind the scroll event to stop further execution
                            $(window).off('scroll');
                        }
                        loading = false;
                    }).fail(function() {
                        alert('Failed to load posts.');
                        loading = false;
                    });
                }
            }

            // Scroll event for loading more posts
            $(window).on('scroll', function() {
                if (!noMorePosts && $(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                    loadPosts();
                }
            });

            // Initial post load
            loadPosts();
        });


        function likePost(event, postId, button) {
            event.stopPropagation();

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

            let shareLinks = shareOptions.map(option => {
                return `<a href="${option.url}" target="_blank" class="btn btn-primary me-2">${option.platform}</a>`;
            }).join('');

            const shareWindow = window.open('', 'Share', 'width=600,height=400');

            // Build the HTML for the share options
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
                      <a href="${facebookUrl}" target="_blank" class="share-button facebook">Facebook</a>
                      <a href="${twitterUrl}" target="_blank" class="share-button twitter">Twitter</a>
                      <a href="${linkedinUrl}" target="_blank" class="share-button linkedin">LinkedIn</a>
                  </div>
              </body>
          </html>
      `);

            // Close the document after writing to ensure proper loading
            shareWindow.document.close();
        }
    </script>

    <script src="vendors/owl-carousel/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>

</body>

</html>