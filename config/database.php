<?php
require_once __DIR__ . '/../includes/error_handling.php';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'drunkies_db');

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'drunkies_db';

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset utf8mb4: " . $conn->error);
    }

    // Set SQL mode to strict
    if (!$conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'")) {
        throw new Exception("Error setting SQL mode: " . $conn->error);
    }

} catch (Exception $e) {
    // Log the error and display a user-friendly message
    error_log("Database connection error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

// Function to safely close the database connection
function closeConnection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// Register the closeConnection function to be called on script termination
register_shutdown_function('closeConnection');
?> 