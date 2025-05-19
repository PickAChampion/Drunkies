<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'drunkies_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4"); 