<?php
/**
 * Delete Candidate Page
 * Handles candidate deletion with confirmation
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
$candidate_id = '';
$error_message = '';
$candidate_data = null;

// Get candidate ID from URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $candidate_id = trim($_GET['id']);
} else {
    header("Location: view_candidates.php?error=invalid_id");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

// Check if candidate exists and get their data
$fetch_query = "SELECT 
    cr.CandidateNationalId,
    cr.FirstName,
    cr.LastName,
    cr.Gender,
    cr.DateOfBirth,
    cr.ExamDate,
    cr.PhoneNumber,
    cr.Marks,
    p.PostName
FROM CandidatesResult cr
JOIN Post p ON cr.PostId = p.PostId
WHERE cr.CandidateNationalId = ?";

$fetch_stmt = $conn->prepare($fetch_query);
$fetch_stmt->bind_param("s", $candidate_id);
$fetch_stmt->execute();
$result = $fetch_stmt->get_result();

if ($result->num_rows == 0) {
    $fetch_stmt->close();
    header("Location: view_candidates.php?error=candidate_not_found");
    exit();
}

$candidate_data = $result->fetch_assoc();
$fetch_stmt->close();

// Process deletion confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Delete the candidate
    $delete_query = "DELETE FROM CandidatesResult WHERE CandidateNationalId = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $candidate_id);
    
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        header("Location: view_candidates.php?success=candidate_deleted");
        exit();
    } else {
        $error_message = "Error deleting candidate. Please try again.";
    }
    
    $delete_stmt->close();
}

$current_user = $_SESSION['UserName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Candidate - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Delete Candidate</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_candidates.php" class="btn btn-secondary">View All Candidates</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Delete Confirmation</h2>
            <p class="subtitle">Are you sure you want to delete this candidate?</p>
            
            <!-- Display error message -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Candidate Information -->
            <div class="candidate-info">
                <h3>Candidate Details:</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>National ID:</strong>
                        <span><?php echo htmlspecialchars($candidate_data['CandidateNationalId']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Full Name:</strong>
                        <span><?php echo htmlspecialchars($candidate_data['FirstName'] . ' ' . $candidate_data['LastName']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Gender:</strong>
                        <span><?php echo htmlspecialchars($candidate_data['Gender']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Date of Birth:</strong>
                        <span><?php echo date('Y-m-d', strtotime($candidate_data['DateOfBirth'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Post:</strong>
                        <span><?php echo htmlspecialchars($candidate_data['PostName']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Exam Date:</strong>
                        <span><?php echo date('Y-m-d', strtotime($candidate_data['ExamDate'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Phone Number:</strong>
                        <span><?php echo htmlspecialchars($candidate_data['PhoneNumber'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Marks:</strong>
                        <span class="marks-display"><?php echo $candidate_data['Marks']; ?>%</span>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action cannot be undone. All candidate information and exam results will be permanently deleted.
                </div>
            </div>
            
            <!-- Deletion Form -->
            <form method="POST" action="delete_candidate.php?id=<?php echo urlencode($candidate_id); ?>">
                <div class="form-actions">
                    <button type="submit" name="confirm_delete" class="btn btn-danger" 
                            onclick="return confirm('This action cannot be undone. Are you absolutely sure you want to delete this candidate?')">
                        Yes, Delete Candidate
                    </button>
                    <a href="view_candidates.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
