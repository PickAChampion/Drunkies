<?php
require_once 'config/database.php';
session_start();

// Check for payment response
if (!isset($_GET['payment_id']) || !isset($_GET['payment_request_id']) || !isset($_SESSION['payment_request_id'])) {
    $_SESSION['error'] = "Invalid payment response.";
    header('Location: index.php');
    exit;
}

$payment_id = $_GET['payment_id'];
$payment_request_id = $_GET['payment_request_id'];
$stored_payment_request_id = $_SESSION['payment_request_id'];

// Validate payment request ID
if ($payment_request_id !== $stored_payment_request_id) {
    $_SESSION['error'] = "Payment verification failed. Invalid payment request.";
    header('Location: index.php');
    exit;
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
    // Get payment details from Instamojo
    $payment_details = $api->paymentRequestPaymentStatus($payment_request_id, $payment_id);
    
    // Get order ID from payment request
    $query = "SELECT order_id FROM payment_requests WHERE payment_request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $payment_request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Payment request not found in database.");
    }
    
    $row = $result->fetch_assoc();
    $order_id = $row['order_id'];
    
    // Handle payment status
    if ($payment_details['payment']['status'] === 'Credit') {
        // Payment successful
        
        // Update payment request
        $update_payment = "UPDATE payment_requests SET 
                           payment_id = ?, 
                           status = 'completed', 
                           updated_at = NOW() 
                           WHERE payment_request_id = ?";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("ss", $payment_id, $payment_request_id);
        $stmt->execute();
        
        // Update order status
        $update_order = "UPDATE orders SET status = 'processing' WHERE id = ?";
        $stmt = $conn->prepare($update_order);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Success message
        $_SESSION['success'] = "Payment successful! Your order is being processed.";
    } else {
        // Payment failed or pending
        
        // Update payment request
        $update_payment = "UPDATE payment_requests SET 
                           payment_id = ?, 
                           status = 'failed', 
                           updated_at = NOW() 
                           WHERE payment_request_id = ?";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("ss", $payment_id, $payment_request_id);
        $stmt->execute();
        
        // Update order status
        $update_order = "UPDATE orders SET status = 'payment_failed' WHERE id = ?";
        $stmt = $conn->prepare($update_order);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Error message
        $_SESSION['error'] = "Payment failed. Please try again or contact support.";
    }
    
    // Clean up session variables
    unset($_SESSION['payment_request_id']);
    unset($_SESSION['order_id']);
    unset($_SESSION['payment_amount']);
    
    // Redirect to order confirmation
    header('Location: order_confirmation.php?id=' . $order_id);
    exit;
    
} catch (Exception $e) {
    // Log error
    $error = $e->getMessage();
    error_log('Payment callback error: ' . $error);
    
    $_SESSION['error'] = "Payment verification error: " . $error;
    
    // Redirect to homepage if we don't have an order ID
    if (isset($order_id)) {
        header('Location: order_confirmation.php?id=' . $order_id);
    } else {
        header('Location: index.php');
    }
    exit;
} 