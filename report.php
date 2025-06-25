<?php
/**
 * Report Page
 * Generate reports for candidates by post with filtering and sorting
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
$selected_post_id = '';
$selected_post_name = '';
$candidates = [];
$error_message = '';

// Get database connection
$conn = getDatabaseConnection();

// Fetch all posts for dropdown
$posts_query = "SELECT PostId, PostName FROM Post ORDER BY PostName ASC";
$posts_result = $conn->query($posts_query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id'])) {
    $selected_post_id = (int)$_POST['post_id'];
    
    if ($selected_post_id > 0) {
        // Get selected post name
        $post_name_query = "SELECT PostName FROM Post WHERE PostId = ?";
        $post_name_stmt = $conn->prepare($post_name_query);
        $post_name_stmt->bind_param("i", $selected_post_id);
        $post_name_stmt->execute();
        $post_name_result = $post_name_stmt->get_result();
        
        if ($post_name_result->num_rows > 0) {
            $selected_post_name = $post_name_result->fetch_assoc()['PostName'];
            
            // Get candidates for selected post using JOIN
            $candidates_query = "SELECT 
                cr.CandidateNationalId,
                CONCAT(cr.FirstName, ' ', cr.LastName) AS FullName,
                cr.FirstName,
                cr.LastName,
                cr.Gender,
                cr.DateOfBirth,
                cr.PhoneNumber,
                cr.Marks,
                cr.ExamDate,
                p.PostName,
                CASE 
                    WHEN cr.Marks >= 90 THEN 'Excellent'
                    WHEN cr.Marks >= 80 THEN 'Very Good'
                    WHEN cr.Marks >= 70 THEN 'Good'
                    WHEN cr.Marks >= 60 THEN 'Pass'
                    ELSE 'Fail'
                END AS Grade
            FROM CandidatesResult cr
            JOIN Post p ON cr.PostId = p.PostId
            WHERE cr.PostId = ?
            ORDER BY cr.Marks DESC, cr.LastName ASC";
            
            $candidates_stmt = $conn->prepare($candidates_query);
            $candidates_stmt->bind_param("i", $selected_post_id);
            $candidates_stmt->execute();
            $candidates_result = $candidates_stmt->get_result();
            
            // Fetch all candidates
            while ($row = $candidates_result->fetch_assoc()) {
                $candidates[] = $row;
            }
            
            $candidates_stmt->close();
        } else {
            $error_message = "Selected post not found.";
        }
        
        $post_name_stmt->close();
    } else {
        $error_message = "Please select a valid post.";
    }
}

// Calculate statistics if candidates exist
$total_candidates = count($candidates);
$passed_candidates = 0;
$total_marks = 0;
$highest_marks = 0;
$lowest_marks = 100;

if ($total_candidates > 0) {
    foreach ($candidates as $candidate) {
        $total_marks += $candidate['Marks'];
        if ($candidate['Marks'] >= 60) {
            $passed_candidates++;
        }
        if ($candidate['Marks'] > $highest_marks) {
            $highest_marks = $candidate['Marks'];
        }
        if ($candidate['Marks'] < $lowest_marks) {
            $lowest_marks = $candidate['Marks'];
        }
    }
}

$average_marks = $total_candidates > 0 ? round($total_marks / $total_candidates, 2) : 0;
$pass_rate = $total_candidates > 0 ? round(($passed_candidates / $total_candidates) * 100, 1) : 0;

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates Report - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Candidates Report</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_candidates.php" class="btn btn-secondary">View All Candidates</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="report-container">
            <!-- Filter Form -->
            <div class="filter-form">
                <h3>Generate Report</h3>
                <form method="POST" action="report.php">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="post_id">Select Post:</label>
                            <select id="post_id" name="post_id" required>
                                <option value="">Choose a post...</option>
                                <?php if ($posts_result && $posts_result->num_rows > 0): ?>
                                    <?php 
                                    $posts_result->data_seek(0); // Reset pointer
                                    while ($post = $posts_result->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $post['PostId']; ?>" 
                                                <?php echo ($selected_post_id == $post['PostId']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($post['PostName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Display error message -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Report Results -->
            <?php if (!empty($selected_post_name)): ?>
                <div class="report-header">
                    <h2 class="report-title">Candidates Report</h2>
                    <p class="report-subtitle">Post: <strong><?php echo htmlspecialchars($selected_post_name); ?></strong></p>
                    <p class="report-subtitle">Generated on: <?php echo date('F j, Y \a\t g:i A'); ?></p>
                </div>

                <?php if ($total_candidates > 0): ?>
                    <!-- Statistics -->
                    <div class="report-stats">
                        <div class="stat-box">
                            <h4>Total Candidates</h4>
                            <div class="stat-value"><?php echo $total_candidates; ?></div>
                        </div>
                        <div class="stat-box">
                            <h4>Passed</h4>
                            <div class="stat-value"><?php echo $passed_candidates; ?></div>
                        </div>
                        <div class="stat-box">
                            <h4>Pass Rate</h4>
                            <div class="stat-value"><?php echo $pass_rate; ?>%</div>
                        </div>
                        <div class="stat-box">
                            <h4>Average Marks</h4>
                            <div class="stat-value"><?php echo $average_marks; ?>%</div>
                        </div>
                        <div class="stat-box">
                            <h4>Highest Score</h4>
                            <div class="stat-value"><?php echo $highest_marks; ?>%</div>
                        </div>
                        <div class="stat-box">
                            <h4>Lowest Score</h4>
                            <div class="stat-value"><?php echo $lowest_marks; ?>%</div>
                        </div>
                    </div>

                    <!-- Print Button -->
                    <button onclick="window.print()" class="print-button">🖨️ Print Report</button>

                    <!-- Candidates Table -->
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Full Name</th>
                                <th>National ID</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Phone Number</th>
                                <th>Marks</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            foreach ($candidates as $candidate): 
                                // Calculate age
                                $birth_date = new DateTime($candidate['DateOfBirth']);
                                $today = new DateTime();
                                $age = $today->diff($birth_date)->y;
                                
                                // Determine grade class for styling
                                $grade_class = '';
                                if ($candidate['Marks'] >= 90) $grade_class = 'grade-excellent';
                                elseif ($candidate['Marks'] >= 80) $grade_class = 'grade-very-good';
                                elseif ($candidate['Marks'] >= 70) $grade_class = 'grade-good';
                                elseif ($candidate['Marks'] >= 60) $grade_class = 'grade-pass';
                                else $grade_class = 'grade-fail';
                            ?>
                                <tr>
                                    <td class="rank-cell"><?php echo $rank; ?></td>
                                    <td class="candidate-name">
                                        <?php echo htmlspecialchars($candidate['FullName']); ?>
                                    </td>
                                    <td class="national-id">
                                        <?php echo htmlspecialchars($candidate['CandidateNationalId']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($candidate['Gender']); ?></td>
                                    <td>
                                        <?php echo date('Y-m-d', strtotime($candidate['DateOfBirth'])); ?>
                                        <small>(<?php echo $age; ?> years)</small>
                                    </td>
                                    <td><?php echo htmlspecialchars($candidate['PhoneNumber'] ?: 'N/A'); ?></td>
                                    <td class="marks-cell"><?php echo $candidate['Marks']; ?>%</td>
                                    <td class="<?php echo $grade_class; ?>">
                                        <?php echo $candidate['Grade']; ?>
                                    </td>
                                </tr>
                            <?php 
                            $rank++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>

                <?php else: ?>
                    <div class="no-results">
                        <h3>No Candidates Found</h3>
                        <p>No candidates have applied for the selected post: <strong><?php echo htmlspecialchars($selected_post_name); ?></strong></p>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <h3>Select a Post</h3>
                    <p>Please select a post from the dropdown above to generate a candidates report.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
