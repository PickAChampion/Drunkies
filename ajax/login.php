<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Set remember me cookie if requested
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
            
            // Store token in database
            $updateQuery = "UPDATE users SET remember_token = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('si', $token, $user['id']);
            $stmt->execute();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $user['is_admin'] ? 'admin/index.php' : 'index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
} 