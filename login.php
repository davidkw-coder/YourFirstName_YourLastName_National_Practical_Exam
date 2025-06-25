<?php
/**
 * User Login Page
 * Handles user authentication and session management
 */

// Start session
session_start();

// Include database connection
require_once 'db.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['UserId'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables
$username = '';
$error_message = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($username)) {
        $error_message = "Username is required.";
    } elseif (empty($password)) {
        $error_message = "Password is required.";
    } else {
        // Get database connection
        $conn = getDatabaseConnection();
        
        // Query to get user data
        $query = "SELECT UserId, UserName, Password FROM Users WHERE UserName = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using password_verify()
            if (password_verify($password, $user['Password'])) {
                // Password is correct, start session
                $_SESSION['UserId'] = $user['UserId'];
                $_SESSION['UserName'] = $user['UserName'];
                $_SESSION['LoginTime'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Login</h2>
            <p class="subtitle">Access the HR Management System</p>
            
            <!-- Display error message -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Display logout message -->
            <?php if (isset($_GET['message']) && $_GET['message'] == 'logged_out'): ?>
                <div class="alert alert-success">
                    You have been successfully logged out.
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>" 
                           required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
