<?php
/**
 * Database Debug Page
 * Check what data exists in the database
 */

// Session check
session_start();
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db.php';

// Get database connection
$conn = getDatabaseConnection();

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Debug - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <h1>Database Debug Information</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Database Status Check</h2>
            
            <!-- Check Posts Table -->
            <div class="debug-section">
                <h3>Posts Table</h3>
                <?php
                $posts_query = "SELECT * FROM Post ORDER BY PostId ASC";
                $posts_result = $conn->query($posts_query);
                
                if (!$posts_result) {
                    echo "<p class='alert alert-error'>Error querying Posts table: " . $conn->error . "</p>";
                } else {
                    echo "<p><strong>Total Posts:</strong> " . $posts_result->num_rows . "</p>";
                    
                    if ($posts_result->num_rows > 0) {
                        echo "<table class='data-table'>";
                        echo "<thead><tr><th>Post ID</th><th>Post Name</th><th>Description</th><th>Created At</th></tr></thead>";
                        echo "<tbody>";
                        while ($post = $posts_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($post['PostId']) . "</td>";
                            echo "<td>" . htmlspecialchars($post['PostName']) . "</td>";
                            echo "<td>" . htmlspecialchars($post['Description'] ?: 'No description') . "</td>";
                            echo "<td>" . htmlspecialchars($post['CreatedAt']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p class='alert alert-warning'>No posts found in database.</p>";
                    }
                }
                ?>
            </div>

            <!-- Check Candidates Table -->
            <div class="debug-section">
                <h3>Candidates Table</h3>
                <?php
                $candidates_query = "SELECT cr.*, p.PostName FROM CandidatesResult cr LEFT JOIN Post p ON cr.PostId = p.PostId ORDER BY cr.CandidateNationalId ASC";
                $candidates_result = $conn->query($candidates_query);
                
                if (!$candidates_result) {
                    echo "<p class='alert alert-error'>Error querying Candidates table: " . $conn->error . "</p>";
                } else {
                    echo "<p><strong>Total Candidates:</strong> " . $candidates_result->num_rows . "</p>";
                    
                    if ($candidates_result->num_rows > 0) {
                        echo "<table class='data-table'>";
                        echo "<thead><tr><th>National ID</th><th>Name</th><th>Post</th><th>Marks</th><th>Created At</th></tr></thead>";
                        echo "<tbody>";
                        while ($candidate = $candidates_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($candidate['CandidateNationalId']) . "</td>";
                            echo "<td>" . htmlspecialchars($candidate['FirstName'] . ' ' . $candidate['LastName']) . "</td>";
                            echo "<td>" . htmlspecialchars($candidate['PostName'] ?: 'Unknown Post') . "</td>";
                            echo "<td>" . htmlspecialchars($candidate['Marks']) . "%</td>";
                            echo "<td>" . htmlspecialchars($candidate['CreatedAt']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p class='alert alert-warning'>No candidates found in database.</p>";
                    }
                }
                ?>
            </div>

            <!-- Check Users Table -->
            <div class="debug-section">
                <h3>Users Table</h3>
                <?php
                $users_query = "SELECT UserId, UserName, CreatedAt FROM Users ORDER BY UserId ASC";
                $users_result = $conn->query($users_query);
                
                if (!$users_result) {
                    echo "<p class='alert alert-error'>Error querying Users table: " . $conn->error . "</p>";
                } else {
                    echo "<p><strong>Total Users:</strong> " . $users_result->num_rows . "</p>";
                    
                    if ($users_result->num_rows > 0) {
                        echo "<table class='data-table'>";
                        echo "<thead><tr><th>User ID</th><th>Username</th><th>Created At</th></tr></thead>";
                        echo "<tbody>";
                        while ($user = $users_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['UserId']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['UserName']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['CreatedAt']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p class='alert alert-warning'>No users found in database.</p>";
                    }
                }
                ?>
            </div>

            <!-- Database Connection Info -->
            <div class="debug-section">
                <h3>Database Connection Info</h3>
                <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                <p><strong>Connection Status:</strong> <span style="color: green;">Connected Successfully</span></p>
                <p><strong>MySQL Version:</strong> <?php echo $conn->server_info; ?></p>
            </div>

            <div class="form-footer">
                <p><a href="dashboard.php">← Back to Dashboard</a></p>
            </div>
        </div>
    </div>

    <style>
    .debug-section {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }
    
    .debug-section h3 {
        color: var(--dark-green);
        margin-bottom: 15px;
        font-size: 18px;
        font-weight: 600;
    }
    
    .debug-section .data-table {
        font-size: 12px;
        margin-top: 15px;
    }
    
    .debug-section .data-table th,
    .debug-section .data-table td {
        padding: 8px 10px;
    }
    </style>
</body>
</html>
