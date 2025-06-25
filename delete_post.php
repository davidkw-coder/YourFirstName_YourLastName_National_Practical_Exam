<?php
/**
 * Delete Post Page
 * Handles post deletion with confirmation
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
$error_message = '';
$post_data = null;

// Get post ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id'];
} else {
    header("Location: view_posts.php?error=invalid_id");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

// Check if post exists and get its data
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
$fetch_stmt->close();

// Check if post is being used by candidates
$usage_query = "SELECT COUNT(*) as candidate_count FROM CandidatesResult WHERE PostId = ?";
$usage_stmt = $conn->prepare($usage_query);
$usage_stmt->bind_param("i", $post_id);
$usage_stmt->execute();
$usage_result = $usage_stmt->get_result();
$candidate_count = $usage_result->fetch_assoc()['candidate_count'];
$usage_stmt->close();

// Process deletion confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    if ($candidate_count > 0) {
        $error_message = "Cannot delete this post because it is associated with {$candidate_count} candidate(s). Please remove or reassign the candidates first.";
    } else {
        // Delete the post
        $delete_query = "DELETE FROM Post WHERE PostId = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $post_id);
        
        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            header("Location: view_posts.php?success=post_deleted");
            exit();
        } else {
            $error_message = "Error deleting post. Please try again.";
        }
        
        $delete_stmt->close();
    }
}

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Delete Job Post</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_posts.php" class="btn btn-secondary">View All Posts</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Delete Confirmation</h2>
            <p class="subtitle">Are you sure you want to delete this post?</p>
            
            <!-- Display error message -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Post Information -->
            <div class="post-info">
                <h3>Post Details:</h3>
                <p><strong>Post ID:</strong> <?php echo htmlspecialchars($post_data['PostId']); ?></p>
                <p><strong>Post Name:</strong> <?php echo htmlspecialchars($post_data['PostName']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($post_data['Description'] ?: 'No description'); ?></p>
                
                <?php if ($candidate_count > 0): ?>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This post is associated with <?php echo $candidate_count; ?> candidate(s).
                        You cannot delete this post until all associated candidates are removed or reassigned.
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($candidate_count == 0): ?>
                <!-- Deletion Form -->
                <form method="POST" action="delete_post.php?id=<?php echo $post_id; ?>">
                    <div class="form-actions">
                        <button type="submit" name="confirm_delete" class="btn btn-danger" 
                                onclick="return confirm('This action cannot be undone. Are you absolutely sure?')">
                            Yes, Delete Post
                        </button>
                        <a href="view_posts.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="form-actions">
                    <a href="view_posts.php" class="btn btn-primary">Back to Posts</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
