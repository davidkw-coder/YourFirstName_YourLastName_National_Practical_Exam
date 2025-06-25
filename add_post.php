<?php
/**
 * Add New Post Page
 * Allows authenticated users to add new job posts
 */

// Session check and database connection
session_start();
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db.php';

// Initialize variables
$post_name = '';
$description = '';
$error_message = '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $post_name = trim($_POST['post_name']);
    $description = trim($_POST['description']);
    
    // Validation
    if (empty($post_name)) {
        $error_message = "Post name is required.";
    } elseif (strlen($post_name) > 100) {
        $error_message = "Post name must not exceed 100 characters.";
    } else {
        // Get database connection
        $conn = getDatabaseConnection();
        
        // Check if connection is successful
        if (!$conn) {
            $error_message = "Database connection failed. Please try again.";
        } else {
            // Check if post name already exists
            $check_query = "SELECT PostId FROM Post WHERE PostName = ?";
            $check_stmt = $conn->prepare($check_query);
            
            if (!$check_stmt) {
                $error_message = "Database error: " . $conn->error;
            } else {
                $check_stmt->bind_param("s", $post_name);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = "Post name already exists. Please choose a different name.";
                } else {
                    // Insert new post into database
                    $insert_query = "INSERT INTO Post (PostName, Description) VALUES (?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    
                    if (!$insert_stmt) {
                        $error_message = "Database error: " . $conn->error;
                    } else {
                        $insert_stmt->bind_param("ss", $post_name, $description);
                        
                        if ($insert_stmt->execute()) {
                            $success_message = "Post added successfully!";
                            $post_name = ''; // Clear form
                            $description = '';
                        } else {
                            $error_message = "Error adding post: " . $insert_stmt->error;
                        }
                        
                        $insert_stmt->close();
                    }
                }
                
                $check_stmt->close();
            }
        }
    }
}

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Post - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Add New Job Post</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_posts.php" class="btn btn-secondary">View All Posts</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Create New Job Post</h2>
            <p class="subtitle">Add a new position to the system</p>
            
            <!-- Display error or success messages -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Post Form -->
            <form method="POST" action="add_post.php">
                <div class="form-group">
                    <label for="post_name">Post Name: <span class="required">*</span></label>
                    <input type="text" id="post_name" name="post_name" 
                           value="<?php echo htmlspecialchars($post_name); ?>" 
                           required maxlength="100" 
                           placeholder="e.g., Software Engineer, Data Analyst">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" 
                              rows="4" maxlength="500"
                              placeholder="Brief description of the position (optional)"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Post</button>
            </form>
            
            <div class="form-footer">
                <p><a href="view_posts.php">← Back to Posts List</a></p>
            </div>
        </div>
    </div>
</body>
</html>
