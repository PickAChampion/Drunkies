<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'drunkies_db';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Read and execute SQL file
$sql = file_get_contents('database.sql');

// Execute multi query
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Database tables and sample data created successfully!<br>";
} else {
    die("Error executing SQL: " . $conn->error);
}

$conn->close();

echo "Setup completed successfully! You can now delete this file.";
?> 