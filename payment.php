<?php
/**
 * Payment Page
 * 
 * This page handles payment processing using Instamojo
 * It creates a payment request and redirects the user to the payment gateway
 */
require_once 'config/database.php';
session_start();

// Check if it's a payment retry
if (isset($_GET['retry']) && is_numeric($_GET['retry'])) {
    $order_id = $_GET['retry'];
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = 'payment.php?retry=' . $order_id;
        $_SESSION['error'] = "Please login to retry your payment.";
        header('Location: login.php');
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get order details
    $query = "SELECT o.*, u.name, u.email, u.phone 
              FROM orders o
              JOIN users u ON o.user_id = u.id
              WHERE o.id = ? AND (o.user_id = ? OR ? = 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $order_id, $user_id, $_SESSION['is_admin']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Order not found or you don't have permission to access it.";
        header('Location: index.php');
        exit;
    }
    
    $order = $result->fetch_assoc();
    
    // Store in session
    $_SESSION['order_id'] = $order_id;
    $_SESSION['payment_amount'] = $order['total_amount'];
    $amount = $order['total_amount'];
    
} else {
    // Check if order is set
    if (!isset($_SESSION['order_id']) || !isset($_SESSION['payment_amount'])) {
        header('Location: index.php');
        exit;
    }
    
    $order_id = $_SESSION['order_id'];
    $amount = $_SESSION['payment_amount'];
    
    // Get order details
    $query = "SELECT o.*, u.name, u.email, u.phone 
              FROM orders o
              JOIN users u ON o.user_id = u.id
              WHERE o.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Order not found.";
        header('Location: index.php');
        exit;
    }
    
    $order = $result->fetch_assoc();
}

// Include Instamojo API
require_once 'instamojo/Instamojo.php';

// Set API credentials - replace with your actual API keys
$api = new Instamojo\Instamojo(
    'test_3fca8eebb469e6292a0003326f6',  // Test API Key
    'test_69ae86fc88c4fccf0809d1641df',  // Test Auth Token
    'https://test.instamojo.com/api/1.1/' // Test API URL
);

try {
    // Create payment request
    $response = $api->paymentRequestCreate([
        'purpose' => 'Order #' . $order_id . ' Payment',
        'amount' => $amount,
        'buyer_name' => $order['name'],
        'email' => $order['email'],
        'phone' => $order['phone'],
        'redirect_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment_callback.php',
        'webhook' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment_webhook.php',
        'allow_repeated_payments' => false,
        'send_email' => true,
        'send_sms' => true,
    ]);
    
    // Store payment request ID for verification
    $_SESSION['payment_request_id'] = $response['id'];
    
    // Log payment request to database
    $payment_query = "INSERT INTO payment_requests (order_id, payment_request_id, amount, status, created_at) 
                     VALUES (?, ?, ?, 'created', NOW())";
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("isd", $order_id, $response['id'], $amount);
    $stmt->execute();
    
    // Update order status
    $update_query = "UPDATE orders SET status = 'pending' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    // Redirect to payment page
    header('Location: ' . $response['longurl']);
    exit;
    
} catch (Exception $e) {
    // Log error
    $error = $e->getMessage();
    error_log('Payment error: ' . $error);
    
    // Update order status to payment failed
    $update_query = "UPDATE orders SET status = 'payment_failed' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $_SESSION['error'] = "Payment processing error: " . $error;
    header('Location: order_confirmation.php?id=' . $order_id);
    exit;
} 