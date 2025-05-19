<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

// Check if product exists in cart
if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    exit;
}

// Get product from database to check stock
$db = Database::getInstance();
$stmt = $db->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check stock
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit;
}

// Update quantity
$_SESSION['cart'][$product_id]['quantity'] = $quantity;

echo json_encode(['success' => true]); 