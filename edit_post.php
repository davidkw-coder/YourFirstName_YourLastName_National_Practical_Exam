<?php
/**
 * Edit Post Page
 * Allows authenticated users to edit existing job posts
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
$post_id = 0;
$post_name = '';
$description = '';
$error_message = '';
$success_message = '';

// Get post ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id'];
} else {
    header("Location: view_posts.php");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

// Fetch existing post data
$fetch_query = "SELECT PostId, PostName, Description FROM Post WHERE PostId = ?";
$fetch_stmt = $conn->prepare($fetch_query);
$fetch_stmt->bind_param("i", $post_id);
$fetch_stmt->execute();
$result = $fetch_stmt->get_result();

if ($result->num_rows == 0) {
    $fetch_stmt->close();
    header("Location: view_posts.php?error=post_not_found");
    exit();
}

$post_data = $result->fetch_assoc();
$post_name = $post_data['PostName'];
$description = $post_data['Description'];
$fetch_stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $new_post_name = trim($_POST['post_name']);
    $new_description = trim($_POST['description']);
    
    // Validation
    if (empty($new_post_name)) {
        $error_message = "Post name is required.";
    } elseif (strlen($new_post_name) > 100) {
        $error_message = "Post name must not exceed 100 characters.";
    } else {
        // Check if new post name already exists (excluding current post)
        $check_query = "SELECT PostId FROM Post WHERE PostName = ? AND PostId != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $new_post_name, $post_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Post name already exists. Please choose a different name.";
        } else {
            // Update post in database
            $update_query = "UPDATE Post SET PostName = ?, Description = ? WHERE PostId = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssi", $new_post_name, $new_description, $post_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Post updated successfully!";
                $post_name = $new_post_name;
                $description = $new_description;
            } else {
                $error_message = "Error updating post. Please try again.";
            }
            
            $update_stmt->close();
        }
        
        $check_stmt->close();
    }
}

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Edit Job Post</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_posts.php" class="btn btn-secondary">View All Posts</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Edit Job Post</h2>
            <p class="subtitle">Update post information (ID: <?php echo $post_id; ?>)</p>
            
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
            
            <!-- Edit Post Form -->
            <form method="POST" action="edit_post.php?id=<?php echo $post_id; ?>">
                <div class="form-group">
                    <label for="post_name">Post Name: <span class="required">*</span></label>
                    <input type="text" id="post_name" name="post_name" 
                           value="<?php echo htmlspecialchars($post_name); ?>" 
                           required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" 
                              rows="4" maxlength="500"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Post</button>
                    <a href="view_posts.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
