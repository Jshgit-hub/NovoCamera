<?php 
session_start();
$conn = mysqli_connect('localhost', 'root','', 'novocamera');

// Check if the user is a superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../Login.php');
    exit();
}

// Variables for pagination
$limit = 25; // Number of entries per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for query

// Variables for filtering and searching
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch logs from the database with pagination, filtering, and searching
function getLogs($conn, $limit, $offset, $role_filter, $search_term) {
    // Joining logs with users to get the role of each user
    $query = "SELECT logs.*, users.role FROM logs 
              LEFT JOIN users ON logs.user_id = users.user_id 
              WHERE 1";

    // Add role filter if set
    if ($role_filter !== '') {
        $query .= " AND users.role = '$role_filter'";
    }

    // Add search term filter if set
    if ($search_term !== '') {
        $query .= " AND logs.username LIKE '%$search_term%'";
    }

    // Order by latest activity and apply pagination
    $query .= " ORDER BY logs.timestamp DESC LIMIT $limit OFFSET $offset";
    
    return mysqli_query($conn, $query);
}

// Get total count for pagination and displaying the number of results
function getTotalLogs($conn, $role_filter, $search_term) {
    $query = "SELECT COUNT(*) AS total FROM logs 
              LEFT JOIN users ON logs.user_id = users.user_id 
              WHERE 1";

    // Add role filter if set
    if ($role_filter !== '') {
        $query .= " AND users.role = '$role_filter'";
    }

    // Add search term filter if set
    if ($search_term !== '') {
        $query .= " AND logs.username LIKE '%$search_term%'";
    }

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Fetch all logs (no pagination)
function getAllLogs($conn, $role_filter, $search_term) {
    $query = "SELECT logs.*, users.role FROM logs 
              LEFT JOIN users ON logs.user_id = users.user_id 
              WHERE 1";
    
    // Add role filter if set
    if ($role_filter !== '') {
        $query .= " AND users.role = '$role_filter'";
    }

    // Add search term filter if set
    if ($search_term !== '') {
        $query .= " AND logs.username LIKE '%$search_term%'";
    }

    $query .= " ORDER BY logs.timestamp DESC";
    
    return mysqli_query($conn, $query);
}

// Get logs for the current page
$logs = getLogs($conn, $limit, $offset, $role_filter, $search_term);

// Get total logs for pagination calculation and for displaying the count
$total_logs = getTotalLogs($conn, $role_filter, $search_term);
$total_pages = ceil($total_logs / $limit);

// Pagination window logic: display 5 pages at a time
$pages_per_window = 5;
$start_page = max(1, $page - ($page % $pages_per_window) + 1); // Start page of the window
$end_page = min($total_pages, $start_page + $pages_per_window - 1); // End page of the window

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
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
                            <h5 class="card-title mb-0 text-light">Activity Logs</h5>
                        </div>
                        <div class="card-body">
                            <!-- Filter and Search Form -->
                            <form method="GET" action="logs.php" class="mb-3">
                                <div class="row">
                                    <!-- Role Filter -->
                                    <div class="col-md-4">
                                        <select name="role" class="form-select">
                                            <option value="">All Roles</option>
                                            <option value="user" <?= $role_filter == 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="superadmin" <?= $role_filter == 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                                        </select>
                                    </div>
                                    <!-- Search -->
                                    <div class="col-md-4">
                                        <input type="text" name="search" class="form-control" placeholder="Search by username" value="<?= $search_term ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">Filter & Search</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Display the number of results found -->
                            <div class="mb-2">
                                <strong><?= $total_logs ?> logs found</strong> 
                                <?php if ($search_term || $role_filter): ?>
                                    (filtered by <?= $role_filter ? 'role: ' . $role_filter : '' ?><?= $search_term ? ($role_filter ? ' and ' : '') . 'search term: "' . $search_term . '"' : '' ?>)
                                <?php endif; ?>
                            </div>

                            <!-- Display Print and CSV Buttons -->
                            <div class="d-flex justify-content-end mb-3">
                                <button class="btn btn-secondary me-2" onclick="handlePrint()">Print Report</button>
                                <button class="btn btn-success" onclick="handleCSV()">Download CSV</button>
                            </div>

                            <!-- Logs Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Log ID</th>
                                            <th>User ID</th>
                                            <th>Username</th>
                                            <th>Action</th>
                                            <th>IP Address</th>
                                            <th>Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            while ($row = mysqli_fetch_assoc($logs)) {
                                                echo "<tr>";
                                                echo "<td>" . $row['id'] . "</td>"; 
                                                echo "<td>" . $row['user_id'] . "</td>";
                                                echo "<td>" . $row['username'] . "</td>";
                                                echo "<td>" . $row['action'] . "</td>";
                                                echo "<td>" . $row['ip_address'] . "</td>";
                                                echo "<td>" . $row['timestamp'] . "</td>";
                                                echo "</tr>";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Links -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Button -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&role=<?= $role_filter ?>&search=<?= $search_term ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Previous Set of Pages Button (shows only when there are previous sets) -->
                                    <?php if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $start_page - 1 ?>&role=<?= $role_filter ?>&search=<?= $search_term ?>">...</a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&role=<?= $role_filter ?>&search=<?= $search_term ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Set of Pages Button (shows only when there are more sets) -->
                                    <?php if ($end_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $end_page + 1 ?>&role=<?= $role_filter ?>&search=<?= $search_term ?>">...</a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Next Button -->
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&role=<?= $role_filter ?>&search=<?= $search_term ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="js/app.js"></script>
    
    <!-- Script to handle Print and CSV -->
    <script>
    function handlePrint() {
        const length = prompt("Fetch logs for:\n1. All logs\n2. Current page logs", "1");
        const preparedBy = prompt("Prepared by:", "Your Name");
        document.body.setAttribute("data-prepared-by", "Prepared by: " + preparedBy);
        document.body.setAttribute("data-created-at", new Date().toLocaleString());

        if (length === "1") {
            fetchAllLogs('print', preparedBy);
        } else {
            printPage(preparedBy);
        }
    }

    function handleCSV() {
        const length = prompt("Fetch logs for:\n1. All logs\n2. Current page logs", "1");
        const preparedBy = prompt("Prepared by:", "Your Name");

        if (length === "1") {
            fetchAllLogs('csv', preparedBy);
        } else {
            downloadCSV(preparedBy);
        }
    }

    function downloadCSV(preparedBy) {
        // Get the table element
        const table = document.querySelector("table");
        let csv = [];
        
        // Get the headers
        let headers = [];
        table.querySelectorAll("thead th").forEach(header => headers.push(header.innerText));
        csv.push(headers.join(","));
        
        // Get the rows
        table.querySelectorAll("tbody tr").forEach(row => {
            let rowData = [];
            row.querySelectorAll("td").forEach(cell => rowData.push(cell.innerText));
            csv.push(rowData.join(","));
        });
        
        // Add footer with prepared by and created at
        const createdAt = "Created at: " + new Date().toLocaleString();
        csv.push(""); // Blank row before footer
        csv.push("Prepared by: " + preparedBy);
        csv.push(createdAt);
        
        // Create CSV file
        const csvFile = new Blob([csv.join("\n")], { type: 'text/csv' });
        const downloadLink = document.createElement("a");
        downloadLink.download = "audit_trail_report.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    function fetchAllLogs(action, preparedBy = '') {
        fetch('fetch_all_logs.php')  // You need to create this endpoint to fetch all logs
            .then(response => response.json())
            .then(data => {
                if (action === 'csv') {
                    let csv = [];
                    csv.push("Log ID,User ID,Username,Action,IP Address,Timestamp");
                    data.forEach(row => {
                        csv.push(`${row.id},${row.user_id},${row.username},${row.action},${row.ip_address},${row.timestamp}`);
                    });
                    csv.push(""); // Blank row before footer
                    csv.push("Prepared by: " + preparedBy);
                    csv.push("Created at: " + new Date().toLocaleString());

                    const csvFile = new Blob([csv.join("\n")], { type: 'text/csv' });
                    const downloadLink = document.createElement("a");
                    downloadLink.download = "audit_trail_report.csv";
                    downloadLink.href = window.URL.createObjectURL(csvFile);
                    downloadLink.style.display = "none";

                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                } else if (action === 'print') {
                    const printWindow = window.open();
                    printWindow.document.write('<html><head><title>Logs</title></head><body>');
                    printWindow.document.write('<h2 style="text-align:center">Audit Trail Report</h2>');
                    printWindow.document.write('<table style="width:100%;border-collapse:collapse;margin-top:20px;"><thead><tr><th>Log ID</th><th>User ID</th><th>Username</th><th>Action</th><th>IP Address</th><th>Timestamp</th></tr></thead><tbody>');
                    data.forEach(row => {
                        printWindow.document.write(`<tr><td>${row.id}</td><td>${row.user_id}</td><td>${row.username}</td><td>${row.action}</td><td>${row.ip_address}</td><td>${row.timestamp}</td></tr>`);
                    });
                    printWindow.document.write('</tbody></table>');
                    printWindow.document.write('<div style="text-align:right;margin-top:40px;font-size:12px;">Prepared by: ' + preparedBy + '<br>Created at: ' + new Date().toLocaleString() + '</div>');
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.print();
                }
            });
    }
    </script>

</body>
</html>
