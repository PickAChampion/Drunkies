<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    $_SESSION['error'] = "Please login to proceed with checkout.";
    header('Location: login.php');
    exit;
}

// Get cart items for the user
$user_id = $_SESSION['user_id'];
$cart_query = "SELECT c.id as cart_id
              FROM carts c
              WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

if ($cart_result->num_rows === 0) {
    $_SESSION['error'] = "Your cart is empty. Please add products to your cart.";
    header('Location: cart.php');
    exit;
}

$cart = $cart_result->fetch_assoc();
$cart_id = $cart['cart_id'];

// Get cart items
$cart_items_query = "SELECT ci.*, p.name, p.price, p.image_url, p.stock, b.name as brand_name
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.id
                    JOIN brands b ON p.brand_id = b.id
                    WHERE ci.cart_id = ?";
$stmt = $conn->prepare($cart_items_query);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Check if cart is empty
if ($cart_items->num_rows === 0) {
    $_SESSION['error'] = "Your cart is empty. Please add products to your cart.";
    header('Location: cart.php');
    exit;
}

// Calculate total
$total = 0;
$cart_products = [];
while ($item = $cart_items->fetch_assoc()) {
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
    $cart_products[] = $item;
}

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Process the order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    
    // Validate stock availability before proceeding
    $stock_error = false;
    $cart_items->data_seek(0); // Reset result pointer to beginning
    
    while ($item = $cart_items->fetch_assoc()) {
        if ($item['quantity'] > $item['stock']) {
            $stock_error = true;
            $_SESSION['error'] = "Sorry, {$item['name']} has only {$item['stock']} units in stock.";
            break;
        }
    }
    
    if (!$stock_error) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $order_query = "INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) 
                           VALUES (?, ?, 'pending', ?, ?)";
            $stmt = $conn->prepare($order_query);
            $stmt->bind_param("idss", $user_id, $total, $address, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items
            $cart_items->data_seek(0); // Reset result pointer
            
            while ($item = $cart_items->fetch_assoc()) {
                // Insert order item
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($item_query);
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
                
                // Update product stock
                $new_stock = $item['stock'] - $item['quantity'];
                $update_stock = "UPDATE products SET stock = ? WHERE id = ?";
                $stmt = $conn->prepare($update_stock);
                $stmt->bind_param("ii", $new_stock, $item['product_id']);
                $stmt->execute();
            }
            
            // Clear cart
            $clear_cart = "DELETE FROM cart_items WHERE cart_id = ?";
            $stmt = $conn->prepare($clear_cart);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // If payment method is 'Instamojo', redirect to payment page
            if ($payment_method === 'online') {
                $_SESSION['order_id'] = $order_id;
                $_SESSION['payment_amount'] = $total;
                header('Location: payment.php');
                exit;
            } else {
                // For cash on delivery, show success message
                $_SESSION['success'] = "Your order has been placed successfully! Order ID: #$order_id";
                header('Location: order_confirmation.php?id=' . $order_id);
                exit;
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = "Error placing order: " . $e->getMessage();
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Checkout</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <h5 class="mb-3">Shipping Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Shipping Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <h5 class="mb-3 mt-4">Payment Method</h5>
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                                <label class="form-check-label" for="payment_cod">
                                    Cash on Delivery
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_online" value="online">
                                <label class="form-check-label" for="payment_online">
                                    Pay Online (Credit/Debit Card, Netbanking, UPI)
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-3">Products (<?php echo count($cart_products); ?>)</h6>
                        <hr>
                        
                        <?php foreach ($cart_products as $product): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                                    <small class="d-block text-muted">
                                        <?php echo $product['quantity']; ?> x ₱<?php echo number_format($product['price'], 2); ?>
                                    </small>
                                </div>
                                <span>₱<?php echo number_format($product['quantity'] * $product['price'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="cart.php" class="btn btn-outline-secondary">Back to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 