<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Basic validation
    if (empty($username) || empty($email)) {
        $_SESSION['error'] = 'Username and email are required.';
        header('Location: users.php');
        exit;
    }

    // Check for duplicate username or email (excluding current user)
    $stmt = $conn->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?');
    $stmt->bind_param('ssi', $username, $email, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = 'Username or email already exists.';
        header('Location: users.php');
        exit;
    }
    $stmt->close();

    // Update user
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, password = ?, is_admin = ? WHERE id = ?');
        $stmt->bind_param('sssii', $username, $email, $hashed_password, $is_admin, $id);
    } else {
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, is_admin = ? WHERE id = ?');
        $stmt->bind_param('ssii', $username, $email, $is_admin, $id);
    }
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update user.';
    }
    $stmt->close();
    header('Location: users.php');
    exit;
} else {
    header('Location: users.php');
    exit;
} 