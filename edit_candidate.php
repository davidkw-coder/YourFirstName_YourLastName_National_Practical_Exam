<?php
/**
 * Edit Candidate Page
 * Allows authenticated users to edit existing candidate information
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

// Get candidate ID from URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $candidate_id = trim($_GET['id']);
} else {
    header("Location: view_candidates.php");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

// Fetch all posts for dropdown
$posts_query = "SELECT PostId, PostName FROM Post ORDER BY PostName ASC";
$posts_result = $conn->query($posts_query);

// Fetch existing candidate data
$fetch_query = "SELECT * FROM CandidatesResult WHERE CandidateNationalId = ?";
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
$national_id = $candidate_data['CandidateNationalId'];
$first_name = $candidate_data['FirstName'];
$last_name = $candidate_data['LastName'];
$gender = $candidate_data['Gender'];
$date_of_birth = $candidate_data['DateOfBirth'];
$exam_date = $candidate_data['ExamDate'];
$post_id = $candidate_data['PostId'];
$phone_number = $candidate_data['PhoneNumber'];
$marks = $candidate_data['Marks'];
$fetch_stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_gender = $_POST['gender'];
    $new_date_of_birth = $_POST['date_of_birth'];
    $new_exam_date = $_POST['exam_date'];
    $new_post_id = (int)$_POST['post_id'];
    $new_phone_number = trim($_POST['phone_number']);
    $new_marks = (int)$_POST['marks'];
    
    // Validation
    if (empty($new_first_name)) {
        $error_message = "First name is required.";
    } elseif (empty($new_last_name)) {
        $error_message = "Last name is required.";
    } elseif (empty($new_gender) || !in_array($new_gender, ['Male', 'Female'])) {
        $error_message = "Please select a valid gender.";
    } elseif (empty($new_date_of_birth)) {
        $error_message = "Date of birth is required.";
    } elseif (empty($new_exam_date)) {
        $error_message = "Exam date is required.";
    } elseif ($new_post_id <= 0) {
        $error_message = "Please select a valid post.";
    } elseif ($new_marks < 0 || $new_marks > 100) {
        $error_message = "Marks must be between 0 and 100.";
    } else {
        // Additional validations
        $birth_date = new DateTime($new_date_of_birth);
        $today = new DateTime();
        $age = $today->diff($birth_date)->y;
        
        if ($age < 18) {
            $error_message = "Candidate must be at least 18 years old.";
        } elseif (new DateTime($new_exam_date) > $today) {
            $error_message = "Exam date cannot be in the future.";
        } else {
            // Verify that the selected post exists
            $post_check_query = "SELECT PostId FROM Post WHERE PostId = ?";
            $post_check_stmt = $conn->prepare($post_check_query);
            $post_check_stmt->bind_param("i", $new_post_id);
            $post_check_stmt->execute();
            $post_result = $post_check_stmt->get_result();
            
            if ($post_result->num_rows == 0) {
                $error_message = "Selected post does not exist.";
            } else {
                // Update candidate in database
                $update_query = "UPDATE CandidatesResult SET FirstName = ?, LastName = ?, Gender = ?, DateOfBirth = ?, PostId = ?, ExamDate = ?, PhoneNumber = ?, Marks = ? WHERE CandidateNationalId = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sssssisss", $new_first_name, $new_last_name, $new_gender, $new_date_of_birth, $new_post_id, $new_exam_date, $new_phone_number, $new_marks_gender, $new_date_of_birth, $new_post_id, $new_exam_date, $new_phone_number, $new_marks, $candidate_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "Candidate information updated successfully!";
                    // Update local variables with new values
                    $first_name = $new_first_name;
                    $last_name = $new_last_name;
                    $gender = $new_gender;
                    $date_of_birth = $new_date_of_birth;
                    $exam_date = $new_exam_date;
                    $post_id = $new_post_id;
                    $phone_number = $new_phone_number;
                    $marks = $new_marks;
                } else {
                    $error_message = "Error updating candidate information. Please try again.";
                }
                
                $update_stmt->close();
            }
            
            $post_check_stmt->close();
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
    <title>Edit Candidate - Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <header class="dashboard-header">
            <h1>Edit Candidate</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($current_user); ?>!</span>
                <a href="view_candidates.php" class="btn btn-secondary">View All Candidates</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <div class="form-container">
            <h2>Edit Candidate Information</h2>
            <p class="subtitle">Update candidate details (ID: <?php echo htmlspecialchars($national_id); ?>)</p>
            
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
            
            <!-- Edit Candidate Form -->
            <form method="POST" action="edit_candidate.php?id=<?php echo urlencode($candidate_id); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="national_id">National ID:</label>
                        <input type="text" id="national_id" name="national_id" 
                               value="<?php echo htmlspecialchars($national_id); ?>" 
                               readonly class="readonly-field">
                        <small>National ID cannot be changed</small>
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
                            <?php 
                            // Reset the result pointer
                            $posts_result->data_seek(0);
                            while ($post = $posts_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $post['PostId']; ?>" 
                                        <?php echo ($post_id == $post['PostId']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($post['PostName']); ?>
                                </option>
                            <?php endwhile; ?>
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
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Candidate</button>
                    <a href="view_candidates.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
