<?php
/**
 * Dashboard Page
 * Main page after successful login - Modern Dashboard Layout
 */

// Session check and database connection
session_start();
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db.php';

// Get user information
$user_id = $_SESSION['UserId'];
$username = $_SESSION['UserName'];
$login_time = isset($_SESSION['LoginTime']) ? date('Y-m-d H:i:s', $_SESSION['LoginTime']) : 'Unknown';

// Get database connection for statistics
$conn = getDatabaseConnection();

// Get total number of candidates
$candidates_query = "SELECT COUNT(*) as total_candidates FROM CandidatesResult";
$candidates_result = $conn->query($candidates_query);
$total_candidates = $candidates_result->fetch_assoc()['total_candidates'];

// Get total number of posts
$posts_query = "SELECT COUNT(*) as total_posts FROM Post";
$posts_result = $conn->query($posts_query);
$total_posts = $posts_result->fetch_assoc()['total_posts'];

// Get average marks
$avg_query = "SELECT AVG(Marks) as avg_marks FROM CandidatesResult";
$avg_result = $conn->query($avg_query);
$avg_marks = round($avg_result->fetch_assoc()['avg_marks'], 2);

// Get recent candidates (last 5)
$recent_candidates_query = "SELECT 
    cr.CandidateNationalId,
    CONCAT(cr.FirstName, ' ', cr.LastName) AS FullName,
    cr.Marks,
    p.PostName,
    cr.CreatedAt
FROM CandidatesResult cr
JOIN Post p ON cr.PostId = p.PostId
ORDER BY cr.CreatedAt DESC
LIMIT 5";
$recent_candidates_result = $conn->query($recent_candidates_query);

// Get candidates by grade distribution
$grade_distribution_query = "SELECT 
    CASE 
        WHEN Marks >= 90 THEN 'Excellent'
        WHEN Marks >= 80 THEN 'Very Good'
        WHEN Marks >= 70 THEN 'Good'
        WHEN Marks >= 60 THEN 'Pass'
        ELSE 'Fail'
    END AS Grade,
    COUNT(*) as count
FROM CandidatesResult
GROUP BY 
    CASE 
        WHEN Marks >= 90 THEN 'Excellent'
        WHEN Marks >= 80 THEN 'Very Good'
        WHEN Marks >= 70 THEN 'Good'
        WHEN Marks >= 60 THEN 'Pass'
        ELSE 'Fail'
    END
ORDER BY count DESC";
$grade_distribution_result = $conn->query($grade_distribution_query);

// Get pass rate
$pass_rate_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Marks >= 60 THEN 1 ELSE 0 END) as passed
FROM CandidatesResult";
$pass_rate_result = $conn->query($pass_rate_query);
$pass_rate_data = $pass_rate_result->fetch_assoc();
$pass_rate = $pass_rate_data['total'] > 0 ? round(($pass_rate_data['passed'] / $pass_rate_data['total']) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dashboard-body">
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <svg width="40" height="40" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="30" cy="30" r="28" fill="#6f4e37" stroke="#3c3b3f" stroke-width="2"/>
                            <path d="M20 25C20 20 25 15 30 15C35 15 40 20 40 25C40 30 35 35 30 35C25 35 20 30 20 25Z" fill="#fdf6e3"/>
                            <circle cx="30" cy="25" r="3" fill="#6f4e37"/>
                            <path d="M25 40C25 38 27 36 30 36C33 36 35 38 35 40C35 42 33 44 30 44C27 44 25 42 25 40Z" fill="#3c3b3f"/>
                        </svg>
                    </div>
                    <h2 class="logo-text">Camellia</h2>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3 class="nav-section-title">Main</h3>
                    <ul class="nav-list">
                        <li class="nav-item active">
                            <a href="dashboard.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 13H11V3H3V13ZM3 21H11V15H3V21ZM13 21H21V11H13V21ZM13 3V9H21V3H13Z" fill="currentColor"/>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">Job Management</h3>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="add_post.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" fill="currentColor"/>
                                </svg>
                                Add New Post
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view_posts.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.89 22 5.99 22H18C19.1 22 20 21.1 20 20V8L14 2ZM18 20H6V4H13V9H18V20Z" fill="currentColor"/>
                                </svg>
                                Manage Posts
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">Candidates</h3>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="add_candidate.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 12C17.21 12 19 10.21 19 8C19 5.79 17.21 4 15 4C12.79 4 11 5.79 11 8C11 10.21 12.79 12 15 12ZM6 10V7H4V10H1V12H4V15H6V12H9V10M15 14C12.33 14 7 15.34 7 18V20H23V18C23 15.34 17.67 14 15 14Z" fill="currentColor"/>
                                </svg>
                                Add Candidate
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="view_candidates.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 4C18.21 4 20 5.79 20 8C20 10.21 18.21 12 16 12C13.79 12 12 10.21 12 8C12 5.79 13.79 4 16 4ZM16 14C18.67 14 24 15.34 24 18V20H8V18C8 15.34 13.33 14 16 14ZM8.5 6C10.43 6 12 7.57 12 9.5C12 11.43 10.43 13 8.5 13C6.57 13 5 11.43 5 9.5C5 7.57 6.57 6 8.5 6ZM8.5 15C11.17 15 16.5 16.34 16.5 19V21H0.5V19C0.5 16.34 5.83 15 8.5 15Z" fill="currentColor"/>
                                </svg>
                                Manage Candidates
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">Reports</h3>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="report.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM9 17H7V10H9V17ZM13 17H11V7H13V17ZM17 17H15V13H17V17Z" fill="currentColor"/>
                                </svg>
                                Generate Reports
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">System</h3>
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="register.php" class="nav-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H11V21H5V3H14V8H21ZM17 12C15.34 12 14 13.34 14 15C14 16.66 15.34 18 17 18C18.66 18 20 16.66 20 15C20 13.34 18.66 12 17 12ZM17 19C15.67 19 13 19.67 13 21V22H21V21C21 19.67 18.33 19 17 19Z" fill="currentColor"/>
                                </svg>
                                Add New User
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                        <span class="user-role">HR Manager</span>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 7L15.59 8.41L18.17 11H8V13H18.17L15.59 15.59L17 17L22 12L17 7ZM4 5H12V3H4C2.9 3 2 3.9 2 5V19C2 20.1 2.9 21 4 21H12V19H4V5Z" fill="currentColor"/>
                    </svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($username); ?>!</p>
                </div>
                <div class="header-right">
                    <div class="header-stats">
                        <div class="header-stat">
                            <span class="stat-label">Login Time</span>
                            <span class="stat-value"><?php echo date('H:i', $_SESSION['LoginTime']); ?></span>
                        </div>
                        <div class="header-stat">
                            <span class="stat-label">User ID</span>
                            <span class="stat-value">#<?php echo $user_id; ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 4C18.21 4 20 5.79 20 8C20 10.21 18.21 12 16 12C13.79 12 12 10.21 12 8C12 5.79 13.79 4 16 4ZM16 14C18.67 14 24 15.34 24 18V20H8V18C8 15.34 13.33 14 16 14Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $total_candidates; ?></h3>
                            <p class="stat-label">Total Candidates</p>
                            <span class="stat-trend positive">+12% from last month</span>
                        </div>
                    </div>

                    <div class="stat-card secondary">
                        <div class="stat-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.89 22 5.99 22H18C19.1 22 20 21.1 20 20V8L14 2ZM18 20H6V4H13V9H18V20Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $total_posts; ?></h3>
                            <p class="stat-label">Active Job Posts</p>
                            <span class="stat-trend positive">+3 new this week</span>
                        </div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H19C20.11 23 21 22.11 21 21V9ZM19 21H5V3H14V8H19V21ZM7 17V15H17V17H7ZM7 13V11H17V13H7ZM7 9V7H14V9H7Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $avg_marks ?: '0'; ?>%</h3>
                            <p class="stat-label">Average Score</p>
                            <span class="stat-trend neutral">Stable performance</span>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 11H7V9H9V11ZM13 11H11V9H13V11ZM17 11H15V9H17V11ZM19 4H18V2H16V4H8V2H6V4H5C3.89 4 3.01 4.9 3.01 6L3 20C3 21.1 3.89 22 5 22H19C20.1 22 21 21.1 21 20V6C21 4.9 20.1 4 19 4ZM19 20H5V9H19V20Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $pass_rate; ?>%</h3>
                            <p class="stat-label">Pass Rate</p>
                            <span class="stat-trend <?php echo $pass_rate >= 70 ? 'positive' : 'negative'; ?>">
                                <?php echo $pass_rate >= 70 ? 'Above target' : 'Below target'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Recent Candidates -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Candidates</h3>
                            <a href="view_candidates.php" class="card-action">View All</a>
                        </div>
                        <div class="card-content">
                            <?php if ($recent_candidates_result && $recent_candidates_result->num_rows > 0): ?>
                                <div class="candidates-list">
                                    <?php while ($candidate = $recent_candidates_result->fetch_assoc()): ?>
                                        <div class="candidate-item">
                                            <div class="candidate-avatar">
                                                <span><?php echo strtoupper(substr($candidate['FullName'], 0, 2)); ?></span>
                                            </div>
                                            <div class="candidate-info">
                                                <h4 class="candidate-name"><?php echo htmlspecialchars($candidate['FullName']); ?></h4>
                                                <p class="candidate-post"><?php echo htmlspecialchars($candidate['PostName']); ?></p>
                                            </div>
                                            <div class="candidate-score">
                                                <span class="score-badge <?php echo $candidate['Marks'] >= 60 ? 'pass' : 'fail'; ?>">
                                                    <?php echo $candidate['Marks']; ?>%
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <p>No candidates found</p>
                                    <a href="add_candidate.php" class="btn btn-primary btn-sm">Add First Candidate</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Grade Distribution -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Grade Distribution</h3>
                            <a href="report.php" class="card-action">View Reports</a>
                        </div>
                        <div class="card-content">
                            <?php if ($grade_distribution_result && $grade_distribution_result->num_rows > 0): ?>
                                <div class="grade-chart">
                                    <?php while ($grade = $grade_distribution_result->fetch_assoc()): ?>
                                        <div class="grade-item">
                                            <div class="grade-info">
                                                <span class="grade-label"><?php echo $grade['Grade']; ?></span>
                                                <span class="grade-count"><?php echo $grade['count']; ?></span>
                                            </div>
                                            <div class="grade-bar">
                                                <div class="grade-fill <?php echo strtolower(str_replace(' ', '-', $grade['Grade'])); ?>" 
                                                     style="width: <?php echo ($grade['count'] / $total_candidates) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <p>No grade data available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
