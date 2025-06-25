<?php
/**
 * Database Configuration and Connection
 * This file handles the MySQLi database connection for the Camellia application
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'Camellia');

/**
 * Create MySQLi database connection
 * @return mysqli|false Returns MySQLi connection object or false on failure
 */
function getDatabaseConnection() {
    // Create connection
    $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    
    // Set charset to utf8 for proper character encoding
    $connection->set_charset("utf8");
    
    return $connection;
}

/**
 * Close database connection
 * @param mysqli $connection The database connection to close
 */
function closeDatabaseConnection($connection) {
    if ($connection) {
        $connection->close();
    }
}
?>
