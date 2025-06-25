<?php
/**
 * Authentication Check Helper
 * Ensures only logged-in users can access protected pages
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 * Redirects to login page if not authenticated
 */
function requireAuth() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.php?message=access_denied");
        exit();
    }
}

/**
 * Get current user information
 * @return array User information
 */
function getCurrentUser() {
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}
?>
