<?php
// manage.php - Web page for managers to query and manage the eoi table

// --- Configuration ---
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "provocate";

// --- Helpers ---
function getDBConnection() {
    global $dbHost, $dbUser, $dbPass, $dbName;
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_errno) {
        die("Database connection failed: " . $conn->connect_error);
    }
    // Use UTF-8
    $conn->set_charset("utf8mb4");
    return $conn;
}

function sanitize($data) {
    // trim and remove slashes; do not double-encode HTML here since we will encode on output
    return stripslashes(trim($data));
}

/**
 * Execute a SELECT query with prepared statements.
 * Returns mysqli_result on success (may be empty) or false on error.
 */
function executeSelect($conn, $sql, $params = [], $types = "") {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return false;
    }
    if (!empty($params) && $types !== "") {
        // bind parameters
        if (!$stmt->bind_param($types, ...$params)) {
            $stmt->close();
            return false;
        }
    }
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

// --- Main ---
$conn = getDBConnection();

$results = [];
$message = "";
$query_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query_type = isset($_POST['query_type']) ? sanitize($_POST['query_type']) : '';

    switch ($query_type) {
        case 'list_all':
            $result = $conn->query("SELECT * FROM eoi ORDER BY EOInumber");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
                $result->free();
            } else {
                $message = "Error running query: " . $conn->error;
            }
            break;

        case 'list_by_job_ref':
            $job_ref = isset($_POST['job_ref']) ? sanitize($_POST['job_ref']) : '';
            $result = executeSelect($conn, "SELECT * FROM eoi WHERE job_ref = ?", [$job_ref], "s");
            if ($result === false) {
                $message = "Error running query.";
            } else {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
                $result->free();
            }
            break;

        case 'list_by_applicant':
            $firstname_raw = isset($_POST['firstname']) ? sanitize($_POST['firstname']) : '';
            $lastname_raw  = isset($_POST['lastname'])  ? sanitize($_POST['lastname'])  : '';

            // construct LIKE patterns safely
            $firstname = '%' . $firstname_raw . '%';
            $lastname  = '%' . $lastname_raw  . '%';

            $result = executeSelect($conn, "SELECT * FROM eoi WHERE firstname LIKE ? AND lastname LIKE ?", [$firstname, $lastname], "ss");
            if ($result === false) {
                $message = "Error running query.";
            } else {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
                $result->free();
            }
            break;

        case 'delete_by_job_ref':
            $job_ref = isset($_POST['job_ref_delete']) ? sanitize($_POST['job_ref_delete']) : '';
            $stmt = $conn->prepare("DELETE FROM eoi WHERE job_ref = ?");
            if ($stmt === false) {
                $message = "Error preparing delete statement: " . $conn->error;
            } else {
                $stmt->bind_param("s", $job_ref);
                if ($stmt->execute()) {
                    $affected = $stmt->affected_rows;
                    $message = "Deleted $affected EOI(s) with job reference '" . htmlspecialchars($job_ref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'.";
                } else {
                    $message = "Error deleting EOIs: " . $stmt->error;
                }
                $stmt->close();
            }
            break;

        case 'change_status':
            $EOInumber_raw = isset($_POST['EOInumber']) ? sanitize($_POST['EOInumber']) : '';
            $new_status = isset($_POST['new_status']) ? sanitize($_POST['new_status']) : '';

            // validate EOInumber as integer
            $EOInumber = (int)$EOInumber_raw;

            $stmt = $conn->prepare("UPDATE eoi SET status = ? WHERE EOInumber = ?");
            if ($stmt === false) {
                $message = "Error preparing update statement: " . $conn->error;
            } else {
                $stmt->bind_param("si", $new_status, $EOInumber);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = "Status of EOI ID '" . htmlspecialchars((string)$EOInumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "' updated to '" . htmlspecialchars($new_status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'.";
                    } else {
                        $message = "No rows updated (EOI ID may not exist or status unchanged).";
                    }
                } else {
                    $message = "Error updating status: " . $stmt->error;
                }
                $stmt->close();
            }
            break;

        default:
            $message = "Invalid action selected.";
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

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
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

        <?php $query_type_selected = isset($_POST['query_type']) ? htmlspecialchars($_POST['query_type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : ''; ?>

        <?php if ($query_type_selected === 'list_by_job_ref'): ?>
        <div>
            <label for="job_ref">Job Reference:</label>
            <input type="text" name="job_ref" id="job_ref" required>
        </div>
        <?php endif; ?>

        <?php if ($query_type_selected === 'list_by_applicant'): ?>
        <div>
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" required>
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" required>
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
            <label for="EOInumber">EOI ID:</label>
            <input type="number" name="EOInumber" id="EOInumber" required>
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
        <p class="<?php echo (strpos($message, 'Error') !== false) ? 'error' : 'message'; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
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
                    <td><?php echo htmlspecialchars($row['EOInumber'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['job_ref'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['firstname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['lastname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['skills'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && in_array($query_type, ['list_all', 'list_by_job_ref', 'list_by_applicant'])): ?>
        <p>No results found for the selected query.</p>
    <?php endif; ?>
</body>
</html>
