<?php
include("../connection/connection.php");

if (isset($_GET['district_id'])) {
    $district_id = intval($_GET['district_id']);

    // Fetch municipalities within the given district
    $sql_muni = "SELECT * FROM municipalities WHERE District_ID = ?";
    $stmt_muni = $conn->prepare($sql_muni);
    $stmt_muni->bind_param('i', $district_id);
    $stmt_muni->execute();
    $result_muni = $stmt_muni->get_result();
    $municipalities = $result_muni->fetch_all(MYSQLI_ASSOC);

    if ($municipalities) {
        foreach ($municipalities as $municipality) { // Corrected loop variable name
            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../municipality/municipality-details.php?Muni_ID=" . htmlspecialchars($municipality['Muni_ID']) . "' class='card text-center border-0 h-100 card-hover' style='border-radius: 20px; overflow: hidden; text-decoration: none; position: relative;'>";
            echo "<div class='card-img-wrapper' style='overflow: hidden; position: relative; width: 100%; height: 100%;'>";
            // Image with zoom effect
            echo "<img src='" . htmlspecialchars($municipality['MuniPicture']) . "' class='card-img' style='height: 100%; object-fit: cover; width: 100%; transition: transform 0.5s ease;' alt='" . htmlspecialchars($municipality['MuniName']) . "'>";
            // Full-size overlay
            echo "<div class='overlay d-flex align-items-end justify-content-center' style='position: absolute; bottom: 0; left: 0; width: 100%; height: 50px; background: rgba(0, 0, 0, 0.7); transition: background 0.3s ease;'>";
            echo "<h5 class='card-title text-light fw-bold m-0 p-2' style='font-size: 1.2rem; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); transition: transform 0.3s ease, color 0.3s ease;'>" . htmlspecialchars($municipality['MuniName']) . "</h5>";
            echo "</div>";
            echo "</div>"; // Close card-img-wrapper
            echo "</a></div>";
        }
    } else {
        echo "<div class='col-12'><p class='text-center text-light'>No municipalities found.</p></div>";
    }
} else {
    echo "<div class='col-12'><p class='text-center text-light'>Invalid request.</p></div>";
}
