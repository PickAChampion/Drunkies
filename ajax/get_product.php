<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = intval($_GET['id']);

$query = "SELECT p.*, b.name as brand_name, c.name as category_name 
          FROM products p 
          JOIN brands b ON p.brand_id = b.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($product = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $product['id'],
            'name' => $product['name'],
            'brand' => $product['brand_name'],
            'category' => $product['category_name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'image_url' => $product['image_url'],
            'alcohol_content' => $product['alcohol_content'],
            'container_type' => $product['container_type'],
            'volume' => $product['volume'],
            'rating' => $product['rating']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
} 