<?php
// Include your database connection file
include("../connection/connection.php");

// Query to retrieve data from the database
$sql = "SELECT * FROM municipalities"; // Assuming a table named 'municipalities'
$query = mysqli_query($conn, $sql);

// Check if there are results
if (mysqli_num_rows($query) > 0) {
    // Initialize an empty string to store the HTML content
    $htmlContent = '';

    $isActive = true; // To set the first item as active

    // Loop through each row and append the HTML content
    while ($row = mysqli_fetch_assoc($query)) {
        // Determine if the item should be marked as active
        $activeClass = $isActive ? 'active' : '';

        // Append the HTML section with dynamic data to the $htmlContent variable
        $htmlContent .= <<<HTML
        <div class="carousel-item {$activeClass}" style="position: relative; overflow: hidden;">
            <a href="../municipality/municipality-details.php?Muni_ID={$row['Muni_ID']}" style="display: block; text-decoration: none; color: inherit;">
                <div class="card border-0" style="border-radius: 20px; overflow: hidden; transition: transform 0.4s ease-in-out, box-shadow 0.4s ease-in-out; height: 300px;">
                    <div class="img-wrapper" style="position: relative; height: 100%;">
                        <img src="{$row['MuniPicture']}" style="width: 100%; height: 100%; object-fit: cover; display: block; filter: brightness(60%); transition: filter 0.4s ease-in-out;" alt="Image of {$row['MuniName']}">
                        <div class="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); opacity: 0; transition: opacity 0.4s ease-in-out;"></div>
                        <div style="position: absolute; bottom: 0; left: 0; width: 100%; color: #fff; text-align: center; padding: 10px 20px; box-sizing: border-box;">
                            <h5 class="card-title" style="margin: 0; font-size: 1.1rem; font-weight: 600; text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);">{$row['MuniName']}</h5>
                        </div>
                    </div>
                </div>
            </a>
        </div>
HTML;
        $isActive = false; // Only the first item should be active
    }

    // Output the complete HTML content
    echo $htmlContent;
} else {
    // Return an error message if no data is found
    echo json_encode(['success' => false, 'message' => 'No data found.']);
}

// Close the database connection
mysqli_close($conn);
