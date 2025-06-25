<?php
/**
 * Landing Page
 * Welcome page for Camellia HR System - First page users see
 */

// Start session to check if user is already logged in
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['UserId'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Camellia HR System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <div class="landing-container">
        <!-- Header Section -->
        <header class="landing-header">
            <div class="logo-container">
                <!-- Camellia Logo Placeholder -->
                <div class="logo-placeholder">
                    <div class="logo-icon">
                        <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="30" cy="30" r="28" fill="#6f4e37" stroke="#3c3b3f" stroke-width="2"/>
                            <path d="M20 25C20 20 25 15 30 15C35 15 40 20 40 25C40 30 35 35 30 35C25 35 20 30 20 25Z" fill="#fdf6e3"/>
                            <circle cx="30" cy="25" r="3" fill="#6f4e37"/>
                            <path d="M25 40C25 38 27 36 30 36C33 36 35 38 35 40C35 42 33 44 30 44C27 44 25 42 25 40Z" fill="#3c3b3f"/>
                        </svg>
                    </div>
                    <h1 class="logo-text">Camellia</h1>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="landing-main">
            <div class="welcome-section">
                <h2 class="welcome-title">Welcome to Camellia HR System</h2>
                <p class="welcome-subtitle">Manage your hiring with ease!</p>
                <p class="welcome-description">
                    Streamline your recruitment process with our comprehensive HR management solution. 
                    Track candidates, manage job posts, and generate insightful reports all in one place.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="login.php" class="btn btn-primary btn-large">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2C11.1046 2 12 2.89543 12 4C12 5.10457 11.1046 6 10 6C8.89543 6 8 5.10457 8 4C8 2.89543 8.89543 2 10 2Z" fill="currentColor"/>
                        <path d="M6 8C5.44772 8 5 8.44772 5 9V15C5 15.5523 5.44772 16 6 16H14C14.5523 16 15 15.5523 15 15V9C15 8.44772 14.5523 8 14 8H6Z" fill="currentColor"/>
                    </svg>
                    Login
                </a>
                
                <a href="register.php" class="btn btn-secondary btn-large">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2C11.1046 2 12 2.89543 12 4C12 5.10457 11.1046 6 10 6C8.89543 6 8 5.10457 8 4C8 2.89543 8.89543 2 10 2Z" fill="currentColor"/>
                        <path d="M6 8C5.44772 8 5 8.44772 5 9V15C5 15.5523 5.44772 16 6 16H14C14.5523 16 15 15.5523 15 15V9C15 8.44772 14.5523 8 14 8H6Z" fill="currentColor"/>
                        <circle cx="15" cy="5" r="3" fill="currentColor"/>
                        <path d="M15 3V7M13 5H17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Register
                </a>
            </div>

            <!-- Features Section -->
            <div class="features-section">
                <h3 class="features-title">Why Choose Camellia HR?</h3>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="5" y="8" width="30" height="24" rx="2" stroke="#6f4e37" stroke-width="2" fill="none"/>
                                <path d="M5 12H35" stroke="#6f4e37" stroke-width="2"/>
                                <circle cx="12" cy="20" r="2" fill="#6f4e37"/>
                                <path d="M18 19H30M18 22H25" stroke="#6f4e37" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h4>Candidate Management</h4>
                        <p>Efficiently track and manage all your job candidates in one centralized system.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="6" width="24" height="28" rx="2" stroke="#6f4e37" stroke-width="2" fill="none"/>
                                <path d="M12 14H28M12 18H28M12 22H24M12 26H20" stroke="#6f4e37" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h4>Job Post Management</h4>
                        <p>Create, edit, and manage job postings with detailed descriptions and requirements.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="6" y="10" width="28" height="20" rx="2" stroke="#6f4e37" stroke-width="2" fill="none"/>
                                <path d="M10 18L16 24L30 14" stroke="#6f4e37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h4>Detailed Reports</h4>
                        <p>Generate comprehensive reports and analytics to make data-driven hiring decisions.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="landing-footer">
            <p>&copy; 2024 Camellia HR System. All rights reserved.</p>
            <p class="footer-tagline">Brewing success, one hire at a time.</p>
        </footer>
    </div>
</body>
</html>
