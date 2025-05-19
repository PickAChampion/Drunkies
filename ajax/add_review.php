<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add a review']);
    exit;
}

// Validate input
if (!isset($_POST['product_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$productId = intval($_POST['product_id']);
$userId = $_SESSION['user_id'];
$rating = intval($_POST['rating']);
$comment = trim($_POST['comment']);

// Validate rating
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit;
}

// Check if user has already reviewed this product
$checkQuery = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('ii', $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
    exit;
}

// Add the review
$query = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('iiis', $productId, $userId, $rating, $comment);

if ($stmt->execute()) {
    // Update product rating
    $updateQuery = "UPDATE products p 
                   SET rating = (
                       SELECT AVG(rating) 
                       FROM reviews 
                       WHERE product_id = ?
                   ) 
                   WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ii', $productId, $productId);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Review added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add review']);
} 