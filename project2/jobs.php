<?php
require_once("settings.php");

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if (!$conn) {
    die("Cannot connect to database");
}

$query = "SELECT * FROM jobs ORDER BY posted_at DESC";
$result = mysqli_query($conn, $query);

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
</head>

<body class="body">
    <div class="container">
        <style>
        aside{
            width: 25%;
            float: right;
            margin: 2%;
            padding: 1%;
            border:#2F4F4F double;
        }   

        footer{
            font-size: small;
            text-align: center;
            width: 100%;
        }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .jobs-title { margin-top: 40px; }
    </style>

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

    <section class="job-section">
        <h2 class="job-title">Data Analyst (GTX-01)</h2>
        <aside class="job-aside">Data Analysts collect, process, and analyze data to help organizations make informed business decisions.</aside>
        <ol class="job-list">
            <li class="job-list-item"><h3 class="job-heading">Salary range</h3></li>
            <p class="job-salary">$50,000 - $90,000 per year (depending on experience and location)</p>
            <li class="job-list-item"><h3 class="job-heading">Positions</h3></li>
            <ul class="job-positions">
                <li class="job-position">Junior Data Analyst</li>
                <li class="job-position">Senior Data Analyst</li>
                <li class="job-position">Business Intelligence Analyst</li>
                <li class="job-position">Data Visualization Specialist</li>
                <li class="job-position">Quantitative Analyst</li>   
            </ul>
            <li class="job-list-item"><h3 class="job-heading">Details</h3></li>
            <ul class="job-details">
                <li class="job-detail">Gather data from multiple sources (databases, spreadsheets, APIs).</li>
                <li class="job-detail">Clean, preprocess, and validate data for accuracy.</li>
                <li class="job-detail">Use statistical techniques to identify trends and patterns.</li>
                <li class="job-detail">Create dashboards, reports, and visualizations (using tools like Excel, Tableau, Power BI).</li>
                <li class="job-detail">Support decision-making by providing actionable insights.</li>
                <li class="job-detail">Collaborate with cross-functional teams to understand data needs.</li>
            </ul>
            <li class="job-list-item"><h3 class="job-heading">Skills required</h3></li>
            <ul class="job-skills">
                <li class="job-skill">Proficiency in data analysis tools (Excel, SQL, Python, R).</li>
                <li class="job-skill">Strong analytical and problem-solving skills.</li>
                <li class="job-skill">Experience with data visualization tools (Tableau, Power BI).</li>
                <li class="job-skill">Knowledge of statistical methods and techniques.</li>
                <li class="job-skill">Attention to detail and accuracy.</li>
                <li class="job-skill">Good communication skills to present findings effectively.</li>
            </ul>
            <li class="job-list-item"><h3 class="job-heading">Career value</h3></li>
            <ul class="job-values">
                <li class="job-value">High demand across various industries (finance, healthcare, marketing).</li>
                <li class="job-value">Competitive salary and benefits.</li>
                <li class="job-value">Opportunities for career growth and advancement.</li>
                <li class="job-value">Ability to work in diverse roles (business intelligence, data science).</li>
                <li class="job-value">Chance to make a significant impact on business decisions.</li> 
            </ul>
        </ol>
    </section>

    <section class="job-section">
        <h2 class="job-title">Software Developer (GTX-02)</h2>
        <aside class="job-aside">Software Developers design, develop, and maintain software applications to meet user needs and business requirements.</aside>
        <ol class="job-list">
            <li class="job-list-item"><h3 class="job-heading">Salary range</h3></li>
            <p class="job-salary">$70,000 - $120,000 per year (depending on experience and location)</p>
            <li class="job-list-item"><h3 class="job-heading">Positions</h3></li>
            <ul class="job-positions">        
                <li class="job-position">Front-End Developer</li>
                <li class="job-position">Back-End Developer</li>
                <li class="job-position">Full-Stack Developer</li>
                <li class="job-position">Mobile App Developer</li>
                <li class="job-position">DevOps Engineer</li>    
            </ul>
            <li class="job-list-item"><h3 class="job-heading">Details</h3></li>      
            <ul class="job-details">
                <li class="job-detail">Write clean, efficient, and maintainable code (using languages like Java, Python, C#, JavaScript).</li>
                <li class="job-detail">Develop and test software applications based on specifications.</li>
                <li class="job-detail">Debug and troubleshoot issues in existing software.</li>
                <li class="job-detail">Collaborate with cross-functional teams (designers, testers, product managers).</li>
                <li class="job-detail">Participate in code reviews and provide constructive feedback.</li>
                <li class="job-detail">Stay updated with the latest industry trends and technologies.</li>
            </ul>
            <li class="job-list-item"><h3 class="job-heading">Skills required</h3></li>      
            <ul class="job-skills">
                <li class="job-skill">Proficiency in programming languages (Java, Python, C#, JavaScript).</li>
                <li class="job-skill">Strong problem-solving and analytical skills.</li>
                <li class="job-skill">Experience with software development methodologies (Agile, Scrum).</li>
                <li class="job-skill">Knowledge of databases and SQL.</li>
                <li class="job-skill">Familiarity with version control systems (Git).</li>
                <li class="job-skill">Good communication and teamwork skills.</li>
            </ul>
            <li class="job-list-item"><h3 class="job-heading">Career value</h3></li>
            <ul class="job-values">
                <li class="job-value">High demand in various industries (technology, finance, healthcare).</li>
                <li class="job-value">Competitive salary and benefits.</li>
                <li class="job-value">Opportunities for career growth and specialization (front-end, back-end, full-stack).</li>
                <li class="job-value">Ability to work on diverse projects and technologies.</li>
                <li class="job-value">Chance to contribute to innovative solutions and products.</li>
            </ul>
        </ol>
    </section>

    <?php include 'footer.inc'; ?>
    </div>
</body>
</html>


    
