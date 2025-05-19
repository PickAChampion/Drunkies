<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

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

// Get product from database
$db = Database::getInstance();
$stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1");
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

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or update cart item
if (isset($_SESSION['cart'][$product_id])) {
    $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }
    $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity
    ];
}

echo json_encode(['success' => true]); 