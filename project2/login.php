<?php
// login.php - Manager login page
// Start the session to store login information
session_start();

// Include database connection settings
require_once("settings.php");

// Variables to store error messages
$error_message = "";
$lockout_message = "";

// Function to clean user input (prevent XSS attacks)
function sanitize($data) {
    $data = trim($data);  // Remove spaces from start and end
    $data = stripslashes($data);  // Remove backslashes
    $data = htmlspecialchars($data);  // Convert special characters to HTML entities
    return $data;
}

// Function to get the user's IP address
function getClientIP() {
    // Try to get IP address from different sources
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get username and password from form
    if (isset($_POST['username'])) {
        $username = sanitize($_POST['username']);
    } else {
        $username = "";
    }
    
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $password = "";
    }
    
    // Connect to database
    $conn = @mysqli_connect($host, $user, $pwd, $sql_db);
    
    // Check if connection worked
    if (!$conn) {
        $error_message = "Database connection failed. Please try again later.";
    } else {
        // Create managers table if it doesn't exist
        $create_managers_table = "CREATE TABLE IF NOT EXISTS managers (
            manager_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $create_managers_table);
        
        // Create login_attempts table if it doesn't exist
        $create_attempts_table = "CREATE TABLE IF NOT EXISTS login_attempts (
            attempt_id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success TINYINT(1) DEFAULT 0
        )";
        mysqli_query($conn, $create_attempts_table);
        
        // Get user's IP address
        $ip_address = getClientIP();
        
        // Check how many failed attempts this IP has in the last 15 minutes
        // Escape the IP address to prevent SQL injection
        $ip_address_escaped = mysqli_real_escape_string($conn, $ip_address);
        $check_query = "SELECT COUNT(*) as attempt_count FROM login_attempts 
                        WHERE ip_address = '$ip_address_escaped' 
                        AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                        AND success = 0";
        $result = mysqli_query($conn, $check_query);
        $row = mysqli_fetch_assoc($result);
        $attempt_count = $row['attempt_count'];
        
        // If 3 or more failed attempts, lock them out
        if ($attempt_count >= 3) {
            $lockout_message = "Too many failed login attempts. Please try again in 15 minutes.";
        } else {
            // Check if username and password were entered
            if (empty($username) || empty($password)) {
                $error_message = "Please enter both username and password.";
                $insert_attempt = "INSERT INTO login_attempts (ip_address, success) VALUES ('$ip_address_escaped', 0)";
                mysqli_query($conn, $insert_attempt);
            } else {
                $username_escaped = mysqli_real_escape_string($conn, $username);
                $login_query = "SELECT manager_id, username, password FROM managers WHERE username = '$username_escaped'";
                $result = mysqli_query($conn, $login_query);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $stored_password = $row['password'];
                    $manager_id = $row['manager_id'];
                    if (password_verify($password, $stored_password)) {
                        $_SESSION['manager_logged_in'] = true;
                        $_SESSION['manager_id'] = $manager_id;
                        $_SESSION['manager_username'] = $username;
                        $delete_attempts = "DELETE FROM login_attempts WHERE ip_address = '$ip_address_escaped'";
                        mysqli_query($conn, $delete_attempts);
                        $insert_attempt = "INSERT INTO login_attempts (ip_address, success) VALUES ('$ip_address_escaped', 1)";
                        mysqli_query($conn, $insert_attempt);
                        mysqli_close($conn);
                        header("Location: manage.php");
                        exit();
                    } else {
                        $error_message = "Invalid username or password.";
                        $insert_attempt = "INSERT INTO login_attempts (ip_address, success) VALUES ('$ip_address_escaped', 0)";
                        mysqli_query($conn, $insert_attempt);
                    }
                } else {
                    $error_message = "Invalid username or password.";
                    $insert_attempt = "INSERT INTO login_attempts (ip_address, success) VALUES ('$ip_address_escaped', 0)";
                    mysqli_query($conn, $insert_attempt);
                }
            }
        }
        
        // Close database connection
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login</title>
    <style>
        .home-button {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .home-button a {
            display: inline-block;
            padding: 10px 16px;
            background-color: #607D8B;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .home-button a:hover {
            background-color: #546E7A;
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0b7dda;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 5px;
        }
        .lockout {
            color: orange;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fff3e0;
            border-radius: 5px;
            font-weight: bold;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #2196F3;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="home-button">
        <a href="index.php">&larr; Return to Home</a>
    </div>
    <div class="container">
        <h1>Manager Login</h1>
        
        <?php 
        // Show lockout message if user is locked out
        if (!empty($lockout_message)) {
            echo "<div class='lockout'>";
            echo $lockout_message;
            echo "</div>";
        }
        ?>
        
        <?php 
        // Show error message if there was an error
        if (!empty($error_message)) {
            echo "<div class='error'>";
            echo $error_message;
            echo "</div>";
        }
        ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <?php 
            // Disable submit button if locked out
            if (!empty($lockout_message)) {
                echo "<input type='submit' value='Login' disabled>";
            } else {
                echo "<input type='submit' value='Login'>";
            }
            ?>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register_manager.php">Register here</a>
        </div>
    </div>
</body>
</html>
