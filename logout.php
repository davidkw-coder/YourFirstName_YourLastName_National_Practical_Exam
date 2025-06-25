<?php
/**
 * User Logout
 * Destroys the user session and redirects to login page
 */

// Session check and database connection
session_start();
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Destroy all session data
session_unset();
session_destroy();

// Delete session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page with logout message
header("Location: login.php?message=logged_out");
exit();
?>
