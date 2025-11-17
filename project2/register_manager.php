<?php
// register_manager.php - Manager registration page
session_start();

require_once("settings.php");

// Array to store any errors
$errors = array();
$success_message = "";

// Function to clean user input
function sanitize($data) {
    $data = trim($data);  // Remove spaces from start and end
    $data = stripslashes($data);  // Remove backslashes
    $data = htmlspecialchars($data);  // Convert special characters
    return $data;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
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
    
    if (isset($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];
    } else {
        $confirm_password = "";
    }
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } else {
        // Check username length
        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long.";
        }
        // Check username contains only allowed characters
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
            $errors[] = "Username can only contain letters, numbers, and underscores.";
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } else {
        // Check password length
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        // Check if password has at least one number
        if (!preg_match("/[0-9]/", $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        // Check if password has at least one letter
        if (!preg_match("/[a-zA-Z]/", $password)) {
            $errors[] = "Password must contain at least one letter.";
        }
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // If no errors, try to save to database
    if (empty($errors)) {
        // Connect to database
        $conn = @mysqli_connect($host, $user, $pwd, $sql_db);
        
        if (!$conn) {
            $errors[] = "Database connection failed. Please try again later.";
        } else {
            // Create managers table if it doesn't exist
            $create_table = "CREATE TABLE IF NOT EXISTS managers (
                manager_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            mysqli_query($conn, $create_table);
            
            // Escape username to prevent SQL injection
            $username_escaped = mysqli_real_escape_string($conn, $username);
            
            // Check if username already exists
            $check_query = "SELECT username FROM managers WHERE username = '$username_escaped'";
            $result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($result) > 0) {
                // Username already exists
                $errors[] = "Username already exists. Please choose a different username.";
            } else {
                // Username is available, so create the account
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Escape hashed password (though it's already safe, this is good practice)
                $hashed_password_escaped = mysqli_real_escape_string($conn, $hashed_password);
                
                // Insert new manager into database
                $insert_query = "INSERT INTO managers (username, password) VALUES ('$username_escaped', '$hashed_password_escaped')";
                
                if (mysqli_query($conn, $insert_query)) {
                    // Registration successful!
                    $success_message = "Registration successful! You can now <a href='login.php'>login</a>.";
                } else {
                    // Something went wrong
                    $errors[] = "Registration failed. Please try again.";
                }
            }
            
            // Close database connection
            mysqli_close($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Registration</title>
    <style>
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
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 5px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 5px;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manager Registration</h1>
        
        <?php 
        // Show errors if there are any
        if (!empty($errors)) {
            echo "<div class='error'>";
            echo "<strong>Please fix the following errors:</strong>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>" . $error . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        ?>
        
        <?php 
        // Show success message if registration was successful
        if (!empty($success_message)) {
            echo "<div class='success'>";
            echo $success_message;
            echo "</div>";
        } else {
            // Show registration form
        ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                    <div class="password-requirements">
                        Password must be at least 8 characters long and contain at least one letter and one number.
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                
                <input type="submit" value="Register">
            </form>
        <?php 
        } // End of else block
        ?>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
