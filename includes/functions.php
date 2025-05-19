<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/error_handling.php';
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    global $conn;
    $userId = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function redirect($location) {
    header("Location: $location");
    exit();
}

function sanitize($input) {
    return htmlspecialchars(strip_tags($input));
}

function generateToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function uploadImage($file, $directory = 'uploads/') {
    $target_dir = __DIR__ . '/../' . $directory;
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $directory . $new_filename;
    }
    return false;
}

// Get all brands
function getAllBrands() {
    global $conn;
    $query = "SELECT * FROM brands ORDER BY name";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all categories
function getAllCategories() {
    global $conn;
    $query = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}
?> 