<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = intval($_GET['product_id']);

$query = "SELECT r.*, u.username 
          FROM reviews r 
          JOIN users u ON r.user_id = u.id 
          WHERE r.product_id = ? 
          ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($review = $result->fetch_assoc()) {
    $reviews[] = [
        'id' => $review['id'],
        'username' => $review['username'],
        'rating' => $review['rating'],
        'comment' => $review['comment'],
        'created_at' => date('M d, Y', strtotime($review['created_at']))
    ];
}

echo json_encode(['success' => true, 'reviews' => $reviews]); 