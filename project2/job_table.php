<?php
// job_table.php - Show jobs from database
require_once("settings.php");

// Connect to database
$conn = mysqli_connect($host, $user, $pwd, $sql_db);

// Check if connection worked
if (!$conn) {
    die("Cannot connect to database");
}

// Get all jobs from the jobs table
$query = "SELECT * FROM jobs ORDER BY posted_at DESC";
$result = mysqli_query($conn, $query);

// Check if query worked
if (!$result) {
    die("Error getting jobs: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles/styles.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/Companylogo.png">
    <title>Jobs Available</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .jobs-title { margin-top: 10px; }
    </style>
</head>

<body class="body">
    <div class="container">
        <?php include 'header.inc'; ?>

        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<h2 class='jobs-title'>All Jobs</h2>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Job Ref</th><th>Title</th><th>Description</th><th>Posted At</th></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                $posted = date('Y-m-d H:i', strtotime($row['posted_at']));
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['job_ref']) . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . htmlspecialchars($posted) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No jobs available right now.</p>";
        }
        mysqli_close($conn);
        ?>
        
        <?php include 'footer.inc'; ?>
    </div>
</body>
</html>