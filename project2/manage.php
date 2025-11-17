<?php
// manage.php - Web page for managers to query and manage the eoi table
// Start session to check if user is logged in
session_start();

// Note: This code expects the eoi table to have columns: eoi_id, job_reference, first_name, last_name, email, phone, skills, status
// If your table uses different column names (like EOInumber, job_ref, firstname, lastname), 
// you may need to update the column names in the SQL queries below

// Check if user is logged in
if (!isset($_SESSION['manager_logged_in'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

if ($_SESSION['manager_logged_in'] !== true) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Function to clean user input
function sanitize($data) {
    $data = trim($data);  // Remove spaces from start and end
    $data = stripslashes($data);  // Remove backslashes
    $data = htmlspecialchars($data);  // Convert special characters
    return $data;
}

// Connect to database
require_once("settings.php");

// Variables to store results and messages
$results = array();
$message = "";
$query_type = "";

// Set default sort field and order
$sort_field = 'EOInumber';
$sort_order = 'ASC';

// Get sort field from form if it was submitted
if (isset($_POST['sort_field'])) {
    $sort_field = sanitize($_POST['sort_field']);
}

// Get sort order from form if it was submitted
if (isset($_POST['sort_order'])) {
    $sort_order = sanitize($_POST['sort_order']);
}

// List of allowed sort fields (to prevent SQL injection)
$valid_sort_fields = array('EOInumber', 'job_ref', 'firstname', 'lastname', 'email', 'phone', 'status');

// Check if sort field is valid
if (!in_array($sort_field, $valid_sort_fields)) {
    $sort_field = 'EOInumber';  // Use default if invalid
}

// Check if sort order is valid
if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
    $sort_order = 'ASC';  // Use default if invalid
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get the query type from form
    if (isset($_POST['query_type'])) {
        $query_type = sanitize($_POST['query_type']);
    }
    
    // Get sort field and order if form was submitted for sorting
    if (isset($_POST['sort_field'])) {
        $sort_field = sanitize($_POST['sort_field']);
        if (!in_array($sort_field, $valid_sort_fields)) {
            $sort_field = 'EOInumber';
        }
    }
    
    if (isset($_POST['sort_order'])) {
        $sort_order = sanitize($_POST['sort_order']);
        if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
            $sort_order = 'ASC';
        }
    }
    
    // Handle different types of queries
    if ($query_type == 'list_all') {
        // List all EOIs
        $sql = "SELECT * FROM eoi ORDER BY $sort_field $sort_order";
        $result = $conn->query($sql);
        
        // Get all rows from result
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    
    elseif ($query_type == 'list_by_job_ref') {
        // List EOIs by job reference
        if (isset($_POST['job_ref'])) {
            $job_ref = sanitize($_POST['job_ref']);
            
            // Use prepared statement for security
            $stmt = $conn->prepare("SELECT * FROM eoi WHERE job_ref = ? ORDER BY $sort_field $sort_order");
            $stmt->bind_param("s", $job_ref);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Get all rows from result
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $stmt->close();
        }
    }
    
    elseif ($query_type == 'list_by_applicant') {
        // List EOIs by applicant name
        if (isset($_POST['first_name']) && isset($_POST['last_name'])) {
            $first_name = "%" . sanitize($_POST['first_name']) . "%";
            $last_name = "%" . sanitize($_POST['last_name']) . "%";
            
            // Use prepared statement for security
            $stmt = $conn->prepare("SELECT * FROM eoi WHERE firstname LIKE ? AND lastname LIKE ? ORDER BY $sort_field $sort_order");
            $stmt->bind_param("ss", $first_name, $last_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Get all rows from result
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $stmt->close();
        }
    }
    
    elseif ($query_type == 'delete_by_job_ref') {
        // Delete EOIs by job reference
        if (isset($_POST['job_ref_delete'])) {
            $job_ref = sanitize($_POST['job_ref_delete']);
            
            // Use prepared statement for security
            $stmt = $conn->prepare("DELETE FROM eoi WHERE job_ref = ?");
            $stmt->bind_param("s", $job_ref);
            
            if ($stmt->execute()) {
                $message = "All EOIs with job reference '$job_ref' have been deleted.";
            } else {
                $message = "Error deleting EOIs: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    elseif ($query_type == 'change_status') {
        // Change status of an EOI
        if (isset($_POST['eoi_id']) && isset($_POST['new_status'])) {
            $eoi_id = sanitize($_POST['eoi_id']);
            $new_status = sanitize($_POST['new_status']);
            
            // Use prepared statement for security
            $stmt = $conn->prepare("UPDATE eoi SET status = ? WHERE EOInumber = ?");
            $stmt->bind_param("si", $new_status, $eoi_id);
            
            if ($stmt->execute()) {
                $message = "Status of EOI ID '$eoi_id' has been updated to '$new_status'.";
            } else {
                $message = "Error updating status: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage EOIs</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
        }
        form { 
            margin-bottom: 20px; 
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .message { 
            color: green; 
            font-weight: bold; 
        }
        .error { 
            color: red; 
            font-weight: bold; 
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }
        .logout-link {
            color: #d32f2f;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #d32f2f;
            border-radius: 5px;
        }
        .logout-link:hover {
            background-color: #d32f2f;
            color: white;
        }
        .user-info {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <h1>Manage Expressions of Interest (EOIs)</h1>
        <div>
            <?php 
            // Show logged in username
            if (isset($_SESSION['manager_username'])) {
                echo "<span class='user-info'>Logged in as: " . htmlspecialchars($_SESSION['manager_username']) . "</span> ";
            } else {
                echo "<span class='user-info'>Logged in as: Manager</span> ";
            }
            ?>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </div>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="query_type">Select Action:</label>
        <select name="query_type" id="query_type" required onchange="this.form.submit()">
            <option value="">-- Choose Action --</option>
            <option value="list_all"<?php echo (isset($_POST['query_type']) && $_POST['query_type'] === 'list_all') ? ' selected' : ''; ?>>List All EOIs</option>
            <option value="list_by_job_ref"<?php echo (isset($_POST['query_type']) && $_POST['query_type'] === 'list_by_job_ref') ? ' selected' : ''; ?>>List EOIs by Job Reference</option>
            <option value="list_by_applicant"<?php echo (isset($_POST['query_type']) && $_POST['query_type'] === 'list_by_applicant') ? ' selected' : ''; ?>>List EOIs by Applicant Name</option>
            <option value="delete_by_job_ref"<?php echo (isset($_POST['query_type']) && $_POST['query_type'] === 'delete_by_job_ref') ? ' selected' : ''; ?>>Delete EOIs by Job Reference</option>
            <option value="change_status"<?php echo (isset($_POST['query_type']) && $_POST['query_type'] === 'change_status') ? ' selected' : ''; ?>>Change Status of an EOI</option>
        </select>
        <br><br>
        
        <?php 
        // Get selected query type to show appropriate fields
        $query_type_selected = "";
        if (isset($_POST['query_type'])) {
            $query_type_selected = sanitize($_POST['query_type']);
        }
        ?>
        
        <?php 
        // Show job reference field if needed
        if ($query_type_selected == 'list_by_job_ref') {
            echo "<div>";
            echo "<label for='job_ref'>Job Reference:</label>";
            echo "<input type='text' name='job_ref' id='job_ref' required>";
            echo "</div>";
        }
        ?>
        
        <?php 
        // Show name fields if needed
        if ($query_type_selected == 'list_by_applicant') {
            echo "<div>";
            echo "<label for='first_name'>First Name:</label>";
            echo "<input type='text' name='first_name' id='first_name' required>";
            echo "<label for='last_name'>Last Name:</label>";
            echo "<input type='text' name='last_name' id='last_name' required>";
            echo "</div>";
        }
        ?>
        
        <?php 
        // Show delete field if needed
        if ($query_type_selected == 'delete_by_job_ref') {
            echo "<div>";
            echo "<label for='job_ref_delete'>Job Reference to Delete:</label>";
            echo "<input type='text' name='job_ref_delete' id='job_ref_delete' required>";
            echo "</div>";
        }
        ?>
        
        <?php 
        // Show status change fields if needed
        if ($query_type_selected == 'change_status') {
            echo "<div>";
            echo "<label for='eoi_id'>EOI ID:</label>";
            echo "<input type='number' name='eoi_id' id='eoi_id' required>";
            echo "<label for='new_status'>New Status:</label>";
            echo "<select name='new_status' id='new_status' required>";
            echo "<option value=''>-- Choose Status --</option>";
            echo "<option value='New'>New</option>";
            echo "<option value='Current'>Current</option>";
            echo "<option value='Final'>Final</option>";
            echo "</select>";
            echo "</div>";
        }
        ?>
        
        <br>
        <input type="submit" value="Submit">
    </form>
    
    <?php 
    // Show message if there is one
    if (!empty($message)) {
        echo "<p class='message'>" . $message . "</p>";
    }
    ?>
    
    <?php 
    // Show results if there are any
    if (!empty($results)) {
        echo "<h2>Query Results</h2>";
        
        // Show sorting form
        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' style='margin-bottom: 20px;'>";
        echo "<input type='hidden' name='query_type' value='" . htmlspecialchars($query_type) . "'>";
        
        // Keep search values when sorting
        if ($query_type == 'list_by_job_ref' && isset($_POST['job_ref'])) {
            echo "<input type='hidden' name='job_ref' value='" . htmlspecialchars($_POST['job_ref']) . "'>";
        }
        if ($query_type == 'list_by_applicant') {
            if (isset($_POST['first_name'])) {
                echo "<input type='hidden' name='first_name' value='" . htmlspecialchars($_POST['first_name']) . "'>";
            }
            if (isset($_POST['last_name'])) {
                echo "<input type='hidden' name='last_name' value='" . htmlspecialchars($_POST['last_name']) . "'>";
            }
        }
        
        // Sort field dropdown
        echo "<label for='sort_field'>Sort by:</label>";
        echo "<select name='sort_field' id='sort_field'>";
        echo "<option value='EOInumber'";
        if ($sort_field == 'EOInumber') echo " selected";
        echo ">EOI Number</option>";
        echo "<option value='job_ref'";
        if ($sort_field == 'job_ref') echo " selected";
        echo ">Job Reference</option>";
        echo "<option value='firstname'";
        if ($sort_field == 'firstname') echo " selected";
        echo ">First Name</option>";
        echo "<option value='lastname'";
        if ($sort_field == 'lastname') echo " selected";
        echo ">Last Name</option>";
        echo "<option value='email'";
        if ($sort_field == 'email') echo " selected";
        echo ">Email</option>";
        echo "<option value='phone'";
        if ($sort_field == 'phone') echo " selected";
        echo ">Phone</option>";
        echo "<option value='status'";
        if ($sort_field == 'status') echo " selected";
        echo ">Status</option>";
        echo "</select>";
        
        // Sort order dropdown
        echo "<label for='sort_order'>Order:</label>";
        echo "<select name='sort_order' id='sort_order'>";
        echo "<option value='ASC'";
        if ($sort_order == 'ASC') echo " selected";
        echo ">Ascending</option>";
        echo "<option value='DESC'";
        if ($sort_order == 'DESC') echo " selected";
        echo ">Descending</option>";
        echo "</select>";
        
        echo "<input type='submit' value='Sort'>";
        echo "</form>";
        
        // Display results table
        echo "<table>";
        echo "<tr>";
        echo "<th>EOI Number</th>";
        echo "<th>Job Reference</th>";
        echo "<th>First Name</th>";
        echo "<th>Last Name</th>";
        echo "<th>Email</th>";
        echo "<th>Phone</th>";
        echo "<th>Skills</th>";
        echo "<th>Status</th>";
        echo "</tr>";
        
        // Loop through results and display each row
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . $row['EOInumber'] . "</td>";
            echo "<td>" . $row['job_ref'] . "</td>";
            echo "<td>" . $row['firstname'] . "</td>";
            echo "<td>" . $row['lastname'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['phone'] . "</td>";
            echo "<td>" . $row['skills'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } 
    // Show message if no results found
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && ($query_type == 'list_all' || $query_type == 'list_by_job_ref' || $query_type == 'list_by_applicant')) {
        echo "<p>No results found for the selected query.</p>";
    }
    ?>
</body>
</html>
