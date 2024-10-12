<?php 
// Connect to the database
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Fetch pending posts
$result = mysqli_query($conn, "SELECT * FROM post WHERE status = 'pending'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Post Approval System">

    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <?php include("includes/adminsidebar.php") ?>

        <div class="main">
            <?php include('includes/navbar-top.php') ?>

            <main class="content">
                <div class="container-fluid p-0">
                    <h1 class="h3 mb-3"><strong>Post Approval</strong> System</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card flex-fill">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Pending Posts</h5>
                                </div>
                                <table class="table table-hover my-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th class="d-none d-xl-table-cell">Title</th>
                                            <th class="d-none d-xl-table-cell">Date Created</th>
                                            <th class="d-none d-md-table-cell">Author</th>
                                            <th class="d-none d-md-table-cell">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['post_id']; ?></td>
                                                <td class="d-none d-xl-table-cell"><?php echo $row['title']; ?></td>
                                                <td class="d-none d-xl-table-cell"><?php echo $row['date_created']; ?></td>
                                                <td class="d-none d-md-table-cell"><?php echo $row['author']; ?></td>
                                                <td class='d-none d-md-table-cell'>
                                                    <a href='approve_post.php?id=<?php echo $row['post_id']; ?>'>Approve</a> | 
                                                    <a href='reject_post.php?id=<?php echo $row['post_id']; ?>'>Reject</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-start">
                            <p class="mb-0">
                                <a class="text-muted" href="https://adminkit.io/" target="_blank"><strong>AdminKit</strong></a> - <a class="text-muted" href="https://adminkit.io/" target="_blank"><strong>Bootstrap Admin Template</strong></a> &copy;
                            </p>
                        </div>
                        <div class="col-6 text-end">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a class="text-muted" href="https://adminkit.io/" target="_blank">Support</a>
                                </li>
                                <li class="list-inline-item">
                                    <a class="text-muted" href="https://adminkit.io/" target="_blank">Help Center</a>
                                </li>
                                <li class="list-inline-item">
                                    <a class="text-muted" href="https://adminkit.io/" target="_blank">Privacy</a>
                                </li>
                                <li class="list-inline-item">
                                    <a class="text-muted" href="https://adminkit.io/" target="_blank">Terms</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>

</html>
