<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: users.php');
        exit;
    }

    // Check for duplicate username or email
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = 'Username or email already exists.';
        header('Location: users.php');
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare('INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $username, $email, $hashed_password, $is_admin);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User added successfully!';
    } else {
        $_SESSION['error'] = 'Failed to add user.';
    }
    $stmt->close();
    header('Location: users.php');
    exit;
} else {
    header('Location: users.php');
    exit;
} 