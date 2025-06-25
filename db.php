<?php
/**
 * Database Connection File
 * Centralized MySQLi database connection for Camellia HR System
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'Camellia');

// Global database connection variable
$conn = null;

/**
 * Create MySQLi database connection
 * @return mysqli|false Returns MySQLi connection object or false on failure
 */
function getDatabaseConnection() {
    global $conn;
    
    // Return existing connection if already established
    if ($conn !== null && !$conn->connect_error) {
        return $conn;
    }
    
    // Create new connection
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database Connection Failed: " . $conn->connect_error);
        die("Database Connection Failed: " . $conn->connect_error . "<br>Please check your database configuration.<br>Host: " . DB_HOST . "<br>Database: " . DB_NAME);
    }
    
    // Set charset to utf8 for proper character encoding
    if (!$conn->set_charset("utf8")) {
        error_log("Error loading character set utf8: " . $conn->error);
        die("Error loading character set utf8: " . $conn->error);
    }
    
    return $conn;
}

/**
 * Close database connection
 * @param mysqli $connection The database connection to close
 */
function closeDatabaseConnection($connection = null) {
    global $conn;
    
    if ($connection) {
        $connection->close();
    } elseif ($conn) {
        $conn->close();
        $conn = null;
    }
}

// Initialize database connection
$conn = getDatabaseConnection();
?>
