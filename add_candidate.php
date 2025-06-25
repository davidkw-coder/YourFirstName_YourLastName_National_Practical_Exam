<?php
/**
 * Add New Candidate Page
 * Allows authenticated users to add new interview candidates
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
$national_id = '';
$first_name = '';
$last_name = '';
$gender = '';
$date_of_birth = '';
$exam_date = '';
$post_id = '';
$phone_number = '';
$marks = '';
$error_message = '';
$success_message = '';

// Get database connection
$conn = getDatabaseConnection();

// Fetch all posts for dropdown
$posts_query = "SELECT PostId, PostName FROM Post ORDER BY PostName ASC";
$posts_result = $conn->query($posts_query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $national_id = trim($_POST['national_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $exam_date = $_POST['exam_date'];
    $post_id = (int)$_POST['post_id'];
    $phone_number = trim($_POST['phone_number']);
    $marks = (int)$_POST['marks'];
    
    // Validation
    if (empty($national_id)) {
        $error_message = "National ID is required.";
    } elseif (strlen($national_id) != 16) {
        $error_message = "National ID must be exactly 16 characters.";
    } elseif (!ctype_digit($national_id)) {
        $error_message = "National ID must contain only numbers.";
    } elseif (empty($first_name)) {
        $error_message = "First name is required.";
    } elseif (empty($last_name)) {
        $error_message = "Last name is required.";
    } elseif (empty($gender) || !in_array($gender, ['Male', 'Female'])) {
        $error_message = "Please select a valid gender.";
    } elseif (empty($date_of_birth)) {
        $error_message = "Date of birth is required.";
    } elseif (empty($exam_date)) {
        $error_message = "Exam date is required.";
    } elseif ($post_id <= 0) {
        $error_message = "Please select a valid post.";
    } elseif ($marks < 0 || $marks > 100) {
        $error_message = "Marks must be between 0 and 100.";
    } else {
        // Additional validations
        $birth_date = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($birth_date)->y;
        
        if ($age < 18) {
            $error_message = "Candidate must be at least 18 years old.";
        } elseif (new DateTime($exam_date) > $today) {
            $error_message = "Exam date cannot be in the future.";
        } else {
            // Check if National ID already exists
            $check_query = "SELECT CandidateNationalId FROM CandidatesResult WHERE CandidateNationalId = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("s", $national_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "A candidate with this National ID already exists.";
            } else {
                // Verify that the selected post exists
                $post_check_query = "SELECT PostId FROM Post WHERE PostId = ?";
                $post_check_stmt = $conn->prepare($post_check_query);
                $post_check_stmt->bind_param("i", $post_id);
                $post_check_stmt->execute();
                $post_result = $post_check_stmt->get_result();
                
                if ($post_result->num_rows == 0) {
                    $error_message = "Selected post does not exist.";
                } else {
                    // Insert new candidate into database
                    $insert_query = "INSERT INTO CandidatesResult (CandidateNationalId, FirstName, LastName, Gender, DateOfBirth, PostId, ExamDate, PhoneNumber, Marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("sssssissi", $national_id, $first_name, $last_name, $gender, $date_of_birth, $post_id, $exam_date, $phone_number, $marks);
                    
                    if ($insert_stmt->execute()) {
                        $success_message = "Candidate added successfully!";
                        // Clear form
                        $national_id = $first_name = $last_name = $gender = $date_of_birth = $exam_date = $phone_number = '';
                        $post_id = $marks = '';
                    } else {
                        $error_message = "Error adding candidate. Please try again.";
                    }
                    
                    $insert_stmt->close();
                }
                
                $post_check_stmt->close();
            }
            
            $check_stmt->close();
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
    <title>Add New Candidate - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Add New Candidate</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_candidates.php" class="btn btn-secondary">View All Candidates</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Register New Candidate</h2>
            <p class="subtitle">Enter candidate information and exam results</p>
            
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
            
            <!-- Add Candidate Form -->
            <form method="POST" action="add_candidate.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="national_id">National ID: <span class="required">*</span></label>
                        <input type="text" id="national_id" name="national_id" 
                               value="<?php echo htmlspecialchars($national_id); ?>" 
                               required maxlength="16" minlength="16"
                               placeholder="16-digit National ID">
                        <small>Must be exactly 16 digits</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name: <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($first_name); ?>" 
                               required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name: <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($last_name); ?>" 
                               required maxlength="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender: <span class="required">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth: <span class="required">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" 
                               value="<?php echo htmlspecialchars($date_of_birth); ?>" 
                               required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                        <small>Must be at least 18 years old</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="post_id">Post: <span class="required">*</span></label>
                        <select id="post_id" name="post_id" required>
                            <option value="">Select Post</option>
                            <?php if ($posts_result && $posts_result->num_rows > 0): ?>
                                <?php while ($post = $posts_result->fetch_assoc()): ?>
                                    <option value="<?php echo $post['PostId']; ?>" 
                                            <?php echo ($post_id == $post['PostId']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($post['PostName']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="exam_date">Exam Date: <span class="required">*</span></label>
                        <input type="date" id="exam_date" name="exam_date" 
                               value="<?php echo htmlspecialchars($exam_date); ?>" 
                               required max="<?php echo date('Y-m-d'); ?>">
                        <small>Cannot be in the future</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="tel" id="phone_number" name="phone_number" 
                               value="<?php echo htmlspecialchars($phone_number); ?>" 
                               maxlength="15" placeholder="+1234567890">
                        <small>Optional - Include country code</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="marks">Marks: <span class="required">*</span></label>
                        <input type="number" id="marks" name="marks" 
                               value="<?php echo htmlspecialchars($marks); ?>" 
                               required min="0" max="100" step="1">
                        <small>Enter marks between 0 and 100</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Candidate</button>
            </form>
            
            <div class="form-footer">
                <p><a href="view_candidates.php">← Back to Candidates List</a></p>
            </div>
        </div>
    </div>
</body>
</html>
