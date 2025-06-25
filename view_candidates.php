<?php
/**
 * View All Candidates Page
 * Display all interview candidates with their information and results
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

// Debug: Check if tables exist and have data
$debug_query = "SELECT COUNT(*) as count FROM CandidatesResult";
$debug_result = $conn->query($debug_query);
if ($debug_result) {
    $debug_count = $debug_result->fetch_assoc();
    // You can uncomment this line for debugging
    // echo "<!-- Debug: Found " . $debug_count['count'] . " candidates in database -->";
}

// Get all candidates with their post information
$query = "SELECT 
    cr.CandidateNationalId,
    cr.FirstName,
    cr.LastName,
    cr.Gender,
    cr.DateOfBirth,
    cr.ExamDate,
    cr.PhoneNumber,
    cr.Marks,
    p.PostName,
    cr.CreatedAt,
    CASE 
        WHEN cr.Marks >= 90 THEN 'Excellent'
        WHEN cr.Marks >= 80 THEN 'Very Good'
        WHEN cr.Marks >= 70 THEN 'Good'
        WHEN cr.Marks >= 60 THEN 'Pass'
        ELSE 'Fail'
    END AS Grade
FROM CandidatesResult cr
JOIN Post p ON cr.PostId = p.PostId
ORDER BY cr.Marks DESC, cr.LastName ASC";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Get statistics with error handling
$stats_query = "SELECT 
    COUNT(*) as total_candidates,
    AVG(Marks) as avg_marks,
    MAX(Marks) as max_marks,
    MIN(Marks) as min_marks,
    SUM(CASE WHEN Marks >= 60 THEN 1 ELSE 0 END) as passed_candidates
FROM CandidatesResult";
$stats_result = $conn->query($stats_query);

if (!$stats_result) {
    die("Stats query failed: " . $conn->error);
}

$stats = $stats_result->fetch_assoc();

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Candidates - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Candidates Management</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="add_candidate.php" class="btn btn-primary">Add New Candidate</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <main class="dashboard-content">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Candidates</h3>
                    <p class="stat-number"><?php echo $stats['total_candidates']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Average Marks</h3>
                    <p class="stat-number"><?php echo round($stats['avg_marks'], 1); ?>%</p>
                </div>
                
                <div class="stat-card">
                    <h3>Highest Score</h3>
                    <p class="stat-number"><?php echo $stats['max_marks']; ?>%</p>
                </div>
                
                <div class="stat-card">
                    <h3>Passed Candidates</h3>
                    <p class="stat-number"><?php echo $stats['passed_candidates']; ?></p>
                </div>
            </div>
            
            <!-- Candidates Table -->
            <div class="table-container">
                <h2>All Candidates</h2>
                
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>National ID</th>
                                    <th>Full Name</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Post</th>
                                    <th>Exam Date</th>
                                    <th>Phone</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    // Calculate age
                                    $birth_date = new DateTime($row['DateOfBirth']);
                                    $today = new DateTime();
                                    $age = $today->diff($birth_date)->y;
                                    
                                    // Determine grade class for styling
                                    $grade_class = '';
                                    if ($row['Marks'] >= 90) $grade_class = 'grade-excellent';
                                    elseif ($row['Marks'] >= 80) $grade_class = 'grade-very-good';
                                    elseif ($row['Marks'] >= 70) $grade_class = 'grade-good';
                                    elseif ($row['Marks'] >= 60) $grade_class = 'grade-pass';
                                    else $grade_class = 'grade-fail';
                                    ?>
                                    <tr>
                                        <td class="national-id"><?php echo htmlspecialchars($row['CandidateNationalId']); ?></td>
                                        <td class="candidate-name">
                                            <?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                                        <td><?php echo $age; ?> years</td>
                                        <td class="post-name"><?php echo htmlspecialchars($row['PostName']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($row['ExamDate'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['PhoneNumber'] ?: 'N/A'); ?></td>
                                        <td class="marks"><?php echo $row['Marks']; ?>%</td>
                                        <td class="<?php echo $grade_class; ?>"><?php echo $row['Grade']; ?></td>
                                        <td class="actions">
                                            <a href="edit_candidate.php?id=<?php echo urlencode($row['CandidateNationalId']); ?>" 
                                               class="btn btn-edit">Edit</a>
                                            <a href="delete_candidate.php?id=<?php echo urlencode($row['CandidateNationalId']); ?>" 
                                               class="btn btn-delete"
                                               onclick="return confirm('Are you sure you want to delete this candidate?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-info">
                        <p>Total Candidates: <strong><?php echo $result->num_rows; ?></strong></p>
                        <p>Pass Rate: <strong><?php echo round(($stats['passed_candidates'] / $stats['total_candidates']) * 100, 1); ?>%</strong></p>
                    </div>
                    
                <?php else: ?>
                    <div class="no-data">
                        <h3>No Candidates Found</h3>
                        <p>There are currently no candidates in the system.</p>
                        <a href="add_candidate.php" class="btn btn-primary">Add First Candidate</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
