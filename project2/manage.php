<?php
// manage.php - Web page for managers to query and manage the eoi table
$host = "localhost";
$user = "root";
$pwd = "";
$sql_db = "provocate";

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
function getDBConnection() {
    $conn = new mysqli("localhost", "root", "", "provocate");
    
}

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function executeQuery($conn, $sql, $params = [], $types = "") {
    $stmt = $conn->prepare($sql);
    if ($params && $types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

$conn = getDBConnection();

$results = [];
$message = "";
$query_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query_type = sanitize($_POST['query_type']);
    
    switch ($query_type) {
        case 'list_all':
            $result = $conn->query("SELECT * FROM eoi ORDER BY eoi_id");
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            break;
            
        case 'list_by_job_ref':
            $job_ref = sanitize($_POST['job_ref']);
            $result = executeQuery($conn, "SELECT * FROM eoi WHERE job_reference = ?", [$job_ref], "s");
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            break;
            
        case 'list_by_applicant':
            $first_name = "%{sanitize{$_POST['first_name']}}%";
            $last_name = "%{sanitize{$_POST['last_name']}}%";
            $result = executeQuery($conn, "SELECT * FROM eoi WHERE first_name LIKE ? AND last_name LIKE ?", [$first_name, $last_name], "ss");
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            break;
            
        case 'delete_by_job_ref':
            $job_ref = sanitize($_POST['job_ref_delete']);
            $stmt = $conn->prepare("DELETE FROM eoi WHERE job_reference = ?");
            $stmt->bind_param("s", $job_ref);
            $message = $stmt->execute() ? "All EOIs with job reference '$job_ref' have been deleted." : "Error deleting EOIs: " . $stmt->error;
            $stmt->close();
            break;
            
        case 'change_status':
            $eoi_id = sanitize($_POST['eoi_id']);
            $new_status = sanitize($_POST['new_status']);
            $stmt = $conn->prepare("UPDATE eoi SET status = ? WHERE eoi_id = ?");
            $stmt->bind_param("si", $new_status, $eoi_id);
            $message = $stmt->execute() ? "Status of EOI ID '$eoi_id' has been updated to '$new_status'." : "Error updating status: " . $stmt->error;
            $stmt->close();
            break;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage EOIs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Manage Expressions of Interest (EOIs)</h1>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="query_type">Select Action:</label>
        <select name="query_type" id="query_type" required>
            <option value="">-- Choose Action --</option>
            <option value="list_all">List All EOIs</option>
            <option value="list_by_job_ref">List EOIs by Job Reference</option>
            <option value="list_by_applicant">List EOIs by Applicant Name</option>
            <option value="delete_by_job_ref">Delete EOIs by Job Reference</option>
            <option value="change_status">Change Status of an EOI</option>
        </select>
        <br><br>
        
        <?php $query_type_selected = isset($_POST['query_type']) ? sanitize($_POST['query_type']) : ''; ?>
        
        <?php if ($query_type_selected === 'list_by_job_ref'): ?>
        <div>
            <label for="job_ref">Job Reference:</label>
            <input type="text" name="job_ref" id="job_ref" required>
        </div>
        <?php endif; ?>
        
        <?php if ($query_type_selected === 'list_by_applicant'): ?>
        <div>
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" required>
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" id="last_name" required>
        </div>
        <?php endif; ?>
        
        <?php if ($query_type_selected === 'delete_by_job_ref'): ?>
        <div>
            <label for="job_ref_delete">Job Reference to Delete:</label>
            <input type="text" name="job_ref_delete" id="job_ref_delete" required>
        </div>
        <?php endif; ?>
        
        <?php if ($query_type_selected === 'change_status'): ?>
        <div>
            <label for="eoi_id">EOI ID:</label>
            <input type="number" name="eoi_id" id="eoi_id" required>
            <label for="new_status">New Status:</label>
            <select name="new_status" id="new_status" required>
                <option value="">-- Choose Status --</option>
                <option value="New">New</option>
                <option value="Current">Current</option>
                <option value="Final">Final</option>
            </select>
        </div>
        <?php endif; ?>
        
        <br>
        <input type="submit" value="Submit">
    </form>
    
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <?php if (!empty($results)): ?>
        <h2>Query Results</h2>
        <table>
            <tr>
                <th>EOI ID</th>
                <th>Job Reference</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Skills</th>
                <th>Status</th>
            </tr>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo $row['eoi_id']; ?></td>
                    <td><?php echo $row['job_reference']; ?></td>
                    <td><?php echo $row['first_name']; ?></td>
                    <td><?php echo $row['last_name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['skills']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && in_array($query_type, ['list_all', 'list_by_job_ref', 'list_by_applicant'])): ?>
        <p>No results found for the selected query.</p>
    <?php endif; ?>
</body>
</html>
