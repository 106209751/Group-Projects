<?php

if (!isset($_POST["firstname"])) {
    header("location: apply.php");
    exit();
}

require_once("settings.php"); 

$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
if (!$conn) {
    die("<p>Database Connection Failed</p>");
}

$create_table = "CREATE TABLE IF NOT EXISTS eoi (
    EOInumber INT AUTO_INCREMENT PRIMARY KEY,
    job_ref VARCHAR(10) NOT NULL,
    firstname VARCHAR(20) NOT NULL,
    lastname VARCHAR(20) NOT NULL,
    street VARCHAR(40) NOT NULL,
    suburb VARCHAR(40) NOT NULL,
    state ENUM('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
    postcode CHAR(4) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(12) NOT NULL,
    skills VARCHAR(255) NOT NULL,
    other_skills TEXT,
    status ENUM('New','Current','Final') DEFAULT 'New'
)";

mysqli_query($conn, $create_table);

// If table already exists with VARCHAR(5), alter it to VARCHAR(10)
$alter_query = "ALTER TABLE eoi MODIFY job_ref VARCHAR(10) NOT NULL";
@mysqli_query($conn, $alter_query); // @ suppresses error if already correct

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$job_ref = sanitize($_POST["job_ref"]);
$firstname = sanitize($_POST["firstname"]);
$lastname = sanitize($_POST["lastname"]);
$street = sanitize($_POST["street"]);
$suburb = sanitize($_POST["suburb"]);
$state = sanitize($_POST["state"]);
$postcode = sanitize($_POST["postcode"]);
$email = sanitize($_POST["email"]);
$phone = sanitize($_POST["phone"]);
// Fix: Check if skills array exists before using it
$skills = isset($_POST['skills']) ? implode(", ", $_POST['skills']) : "";
$other_skills = sanitize($_POST["other_skills"] ?? "");
$errors = [];

if ($job_ref == "") $errors[] = "Job reference is required.";
if (!preg_match("/^[a-zA-Z]{1,20}$/", $firstname)) $errors[] = "First name invalid.";
if (!preg_match("/^[a-zA-Z]{1,20}$/", $lastname)) $errors[] = "Last name invalid.";
if (!preg_match("/^\d{4}$/", $postcode)) $errors[] = "Postcode must be 4 digits.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email format invalid.";
if (!preg_match("/^[0-9 ]{8,12}$/", $phone)) $errors[] = "Phone must be 8–12 digits or spaces.";

if (!empty($errors)) {
    echo "<h2>There were errors with your submission:</h2><ul>";
    foreach ($errors as $error) echo "<li>$error</li>";
    echo "</ul><p><a href='apply.php'>Go back and fix it!</a></p>";
    exit();
}

// Fix: Escape all variables for SQL to prevent injection
$job_ref = mysqli_real_escape_string($conn, $job_ref);
$firstname = mysqli_real_escape_string($conn, $firstname);
$lastname = mysqli_real_escape_string($conn, $lastname);
$street = mysqli_real_escape_string($conn, $street);
$suburb = mysqli_real_escape_string($conn, $suburb);
$state = mysqli_real_escape_string($conn, $state);
$postcode = mysqli_real_escape_string($conn, $postcode);
$email = mysqli_real_escape_string($conn, $email);
$phone = mysqli_real_escape_string($conn, $phone);
$skills = mysqli_real_escape_string($conn, $skills);
$other_skills = mysqli_real_escape_string($conn, $other_skills);

$insert = "INSERT INTO eoi
(job_ref, firstname, lastname, street, suburb, state, postcode, email, phone, 
 skills, other_skills)
VALUES
('$job_ref', '$firstname', '$lastname', '$street', '$suburb', '$state', '$postcode', '$email', '$phone', 
 '$skills', '$other_skills')";

if (mysqli_query($conn, $insert)) {
    $eoi_number = mysqli_insert_id($conn);
    echo "<h2>Application Submitted Successfully!</h2>";
    echo "<p>Your EOI number is: <strong>$eoi_number</strong></p>";
    echo '<p><a href="index.php">Return to Home</a></p>';
} else {
    echo "<p>Oops! Something went wrong. Please try again later.</p>";
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
?>