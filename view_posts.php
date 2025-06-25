<?php
/**
 * View All Posts Page
 * Display all job posts with edit/delete options
 */

// Session check and database connection
session_start();
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db.php';

// Get database connection
$conn = getDatabaseConnection();

// Check if connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Debug: Check if Post table exists and has data
$debug_query = "SELECT COUNT(*) as count FROM Post";
$debug_result = $conn->query($debug_query);
if ($debug_result) {
    $debug_count = $debug_result->fetch_assoc();
    // You can uncomment this line for debugging
    // echo "<!-- Debug: Found " . $debug_count['count'] . " posts in database -->";
}

// Get all posts from database with error handling
$query = "SELECT PostId, PostName, Description, CreatedAt FROM Post ORDER BY PostName ASC";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Job Posts Management</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="add_post.php" class="btn btn-primary">Add New Post</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <main class="dashboard-content">
            <div class="table-container">
                <h2>All Job Posts</h2>
                
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Post ID</th>
                                    <th>Post Name</th>
                                    <th>Description</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['PostId']); ?></td>
                                        <td class="post-name"><?php echo htmlspecialchars($row['PostName']); ?></td>
                                        <td class="description">
                                            <?php 
                                            $desc = htmlspecialchars($row['Description']);
                                            echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                            ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($row['CreatedAt'])); ?></td>
                                        <td class="actions">
                                            <a href="edit_post.php?id=<?php echo $row['PostId']; ?>" 
                                               class="btn btn-edit">Edit</a>
                                            <a href="delete_post.php?id=<?php echo $row['PostId']; ?>" 
                                               class="btn btn-delete"
                                               onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-info">
                        <p>Total Posts: <strong><?php echo $result->num_rows; ?></strong></p>
                    </div>
                    
                <?php else: ?>
                    <div class="no-data">
                        <h3>No Posts Found</h3>
                        <p>There are currently no job posts in the system.</p>
                        <a href="add_post.php" class="btn btn-primary">Add First Post</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
