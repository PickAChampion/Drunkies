<?php
require_once '../includes/db.php';
session_start();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Prevent deleting self (optional, for safety)
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
        $_SESSION['error'] = 'You cannot delete your own account.';
        header('Location: users.php');
        exit;
    }
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete user.';
    }
    $stmt->close();
    header('Location: users.php');
    exit;
} else {
    header('Location: users.php');
    exit;
} 