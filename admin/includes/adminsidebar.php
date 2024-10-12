<?php 
// Function to check if a given sidebar item should be marked as active
function isPageActive($pageNames) {
    // Get the current page URL
    $currentPage = basename($_SERVER['PHP_SELF']);
    // Check if the current page matches any of the given sidebar items
    return in_array($currentPage, (array)$pageNames) ? 'active' : '';
}

// Get the user's role from the session
$role = $_SESSION['role']; 
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="">
            <span class="align-middle">Novocamera</span>
        </a>

        <ul class="sidebar-nav">
            <?php if ($role === 'superadmin'): ?>
                <li class="sidebar-header">Super Admin</li>

                <li class="sidebar-item <?php echo isPageActive('admin_dashboard.php'); ?>">
                    <a class="sidebar-link" href="admin_dashboard.php">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo isPageActive(['cms_intro_about.php', 'cms_services.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#manageContentDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['cms_intro_about.php', 'cms_services.php']) ? 'true' : 'false'; ?>" aria-controls="manageContentDropdown">
                        <i class="align-middle" data-feather="edit"></i>
                        <span class="align-middle">Content Management</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['cms_intro_about.php', 'cms_services.php']) ? 'show' : ''; ?>" id="manageContentDropdown">
                        <li class="sidebar-item <?php echo isPageActive('cms_intro_about.php'); ?>">
                            <a class="sidebar-link" href="cms_intro_about.php">
                                <i class="align-middle" data-feather="file-text"></i> Introduction/About us
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('cms_services.php'); ?>">
                            <a class="sidebar-link" href="cms_services.php">
                                <i class="align-middle" data-feather="briefcase"></i> Services
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item <?php echo isPageActive('logs.php'); ?>">
                    <a class="sidebar-link" href="logs.php">
                        <i class="align-middle" data-feather="clipboard"></i>
                        <span class="align-middle">Logs</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo isPageActive('pages-profile.php'); ?>">
                    <a class="sidebar-link" href="pages-profile.php">
                        <i class="align-middle" data-feather="user"></i>
                        <span class="align-middle">Profile</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo isPageActive('manage-activities.php'); ?>">
                    <a class="sidebar-link" href="manage-activities.php">
                        <i class="align-middle" data-feather="activity"></i>
                        <span class="align-middle">Activities</span>
                    </a>
                </li>

                

                <li class="sidebar-item <?php echo isPageActive(['Manage_gallery.php', 'admin-gallery.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#galleryDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['Manage_gallery.php', 'admin-gallery.php']) ? 'true' : 'false'; ?>" aria-controls="galleryDropdown">
                        <i class="align-middle" data-feather="image"></i>
                        <span class="align-middle">Gallery</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['Manage_gallery.php', 'admin-gallery.php']) ? 'show' : ''; ?>" id="galleryDropdown">
                        <li class="sidebar-item <?php echo isPageActive('Manage_gallery.php'); ?>">
                            <a class="sidebar-link" href="Manage_gallery.php">
                                <i class="align-middle" data-feather="image"></i> Manage Gallery
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('admin-gallery.php'); ?>">
                            <a class="sidebar-link" href="admin-gallery.php">
                                <i class="align-middle" data-feather="upload"></i> Add Images
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Dropdown for Manage Section -->
                <li class="sidebar-item <?php echo isPageActive(['Manage-user.php', 'Manage-admin.php', 'invite_admin.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#manageDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['Manage-user.php', 'Manage-admin.php', 'invite_admin.php']) ? 'true' : 'false'; ?>" aria-controls="manageDropdown">
                        <i class="align-middle" data-feather="settings"></i>
                        <span class="align-middle">Manage</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['Manage-user.php', 'Manage-admin.php', 'invite_admin.php']) ? 'show' : ''; ?>" id="manageDropdown">
                        <li class="sidebar-item <?php echo isPageActive('Manage-user.php'); ?>">
                            <a class="sidebar-link" href="Manage-user.php">
                                <i class="align-middle" data-feather="users"></i> Manage Users
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('Manage-admin.php'); ?>">
                            <a class="sidebar-link" href="Manage-admin.php">
                                <i class="align-middle" data-feather="user-check"></i> Manage Tourism Admin
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('invite_admin.php'); ?>">
                            <a class="sidebar-link" href="invite_admin.php">
                                <i class="align-middle" data-feather="mail"></i> Invite Admin
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item <?php echo isPageActive(['manage_municipality.php', 'add-municipalities.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#municipalitiesDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['manage_municipality.php', 'add-municipalities.php']) ? 'true' : 'false'; ?>" aria-controls="municipalitiesDropdown">
                        <i class="align-middle" data-feather="map"></i>
                        <span class="align-middle">Municipalities</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['manage_municipality.php', 'add-municipalities.php']) ? 'show' : ''; ?>" id="municipalitiesDropdown">
                        <li class="sidebar-item <?php echo isPageActive('manage_municipality.php'); ?>">
                            <a class="sidebar-link" href="manage_municipality.php">
                                <i class="align-middle" data-feather="map-pin"></i> Manage Municipalities
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('add-municipalities.php'); ?>">
                            <a class="sidebar-link" href="add-municipalities.php">
                                <i class="align-middle" data-feather="plus-circle"></i> Add Municipalities
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'superadmin'): ?>
                <li class="sidebar-header">Tourism Admin</li>

                <?php if ($role === 'admin'): ?>
                    <li class="sidebar-item <?php echo isPageActive('pages-profile.php'); ?>">
                        <a class="sidebar-link" href="pages-profile.php">
                            <i class="align-middle" data-feather="user"></i>
                            <span class="align-middle">Profile</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Dropdown for Blogs Section -->
                <li class="sidebar-item <?php echo isPageActive(['manage_blogs.php', 'create_blog.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#blogsDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['manage_blogs.php', 'create_blog.php']) ? 'true' : 'false'; ?>" aria-controls="blogsDropdown">
                        <i class="align-middle" data-feather="book"></i>
                        <span class="align-middle">Blogs</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['manage_blogs.php', 'create_blog.php', 'add_blog_type.php']) ? 'show' : ''; ?>" id="blogsDropdown">
                        <li class="sidebar-item <?php echo isPageActive('manage_blogs.php'); ?>">
                            <a class="sidebar-link" href="manage_blogs.php">
                                <i class="align-middle" data-feather="file-text"></i> Manage Blogs
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('create_blog.php'); ?>">
                            <a class="sidebar-link" href="create_blog.php">
                                <i class="align-middle" data-feather="file-plus"></i> Create blogs
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('add_blog_type.php'); ?>">
                            <a class="sidebar-link" href="add_blog_type.php">
                                <i class="align-middle" data-feather="check-square"></i> Add Blog type
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Dropdown for Posts Section -->
                <li class="sidebar-item <?php echo isPageActive(['Manage-posts.php', 'Logs-approved-reject-post.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#postsDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['Manage-posts.php', 'Logs-approved-reject-post.php']) ? 'true' : 'false'; ?>" aria-controls="postsDropdown">
                        <i class="align-middle" data-feather="file-text"></i>
                        <span class="align-middle">Posts</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['Manage-posts.php', 'Logs-approved-reject-post.php']) ? 'show' : ''; ?>" id="postsDropdown">
                        <li class="sidebar-item <?php echo isPageActive('Manage-posts.php'); ?>">
                            <a class="sidebar-link" href="Manage-posts.php">
                                <i class="align-middle" data-feather="file"></i> Manage Posts
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('Logs-approved-reject-post.php'); ?>">
                            <a class="sidebar-link" href="Logs-approved-reject-post.php">
                                <i class="align-middle" data-feather="check-square"></i> Approved and Rejected Posts
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Dropdown for Places Section -->
                <li class="sidebar-item <?php echo isPageActive(['manage_place.php', 'add_place.php', 'insert_place_type.php']); ?>">
                    <a class="sidebar-link dropdown-toggle" href="#placesDropdown" data-bs-toggle="collapse" aria-expanded="<?php echo isPageActive(['manage_place.php', 'add_place.php', 'insert_place_type.php']) ? 'true' : 'false'; ?>" aria-controls="placesDropdown">
                        <i class="align-middle" data-feather="map"></i>
                        <span class="align-middle">Places</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo isPageActive(['manage_place.php', 'add_place.php', 'insert_place_type.php']) ? 'show' : ''; ?>" id="placesDropdown">
                        <li class="sidebar-item <?php echo isPageActive('manage_place.php'); ?>">
                            <a class="sidebar-link" href="manage_place.php">
                                <i class="align-middle" data-feather="map-pin"></i> Manage Places
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('add_place.php'); ?>">
                            <a class="sidebar-link" href="add_place.php">
                                <i class="align-middle" data-feather="plus-circle"></i> Add Place
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo isPageActive('insert_place_type.php'); ?>">
                            <a class="sidebar-link" href="insert_place_type.php">
                                <i class="align-middle" data-feather="tag"></i> Add Place Type
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
    feather.replace(); // This will replace the data-feather attribute with the actual icons
</script>
