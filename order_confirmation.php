<?php
/**
 * Order Confirmation Page
 * 
 * This page displays order details and payment status after checkout
 * It shows a summary of the purchased items, shipping details, and payment information
 */
require_once 'includes/header.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your order.";
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid order ID.";
    header('Location: index.php');
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order_query = "SELECT o.*, u.name, u.email, u.phone 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ? AND (o.user_id = ? OR ? = 1)";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("iii", $order_id, $user_id, $_SESSION['is_admin']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Order not found or you don't have permission to view it.";
    header('Location: index.php');
    exit;
}

$order = $result->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, p.name, p.image_url, b.name as brand_name 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN brands b ON p.brand_id = b.id
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

// Get payment details if exists
$payment_query = "SELECT * FROM payment_requests WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->num_rows > 0 ? $payment_result->fetch_assoc() : null;

// Get order status text and class
$status_text = ucfirst($order['status']);
$status_badge = match($order['status']) {
    'pending' => 'bg-warning',
    'processing' => 'bg-info',
    'shipped' => 'bg-primary',
    'delivered' => 'bg-success',
    'cancelled' => 'bg-danger',
    'payment_failed' => 'bg-danger',
    default => 'bg-secondary'
};

// Get next steps based on order status
$next_steps = match($order['status']) {
    'pending' => "Your order is pending confirmation. You will receive an update soon.",
    'processing' => "Your order is being processed. It will be shipped soon.",
    'shipped' => "Your order is on the way! You will receive it shortly.",
    'delivered' => "Your order has been delivered. Enjoy your drinks!",
    'cancelled' => "Your order has been cancelled. Please contact support if you have any questions.",
    'payment_failed' => "Your payment was not successful. Please try again or contact support.",
    default => "Your order status will be updated soon."
};
?>

<div class="container my-5">
    <!-- Order Confirmation Header -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <h1 class="display-5 mb-3">
                <?php if ($order['status'] === 'payment_failed'): ?>
                    <i class="fas fa-times-circle text-danger me-2"></i> Payment Failed
                <?php elseif ($order['status'] === 'cancelled'): ?>
                    <i class="fas fa-times-circle text-danger me-2"></i> Order Cancelled
                <?php else: ?>
                    <i class="fas fa-check-circle text-success me-2"></i> Order Confirmed
                <?php endif; ?>
            </h1>
            
            <p class="lead">
                Thank you for your order, <?php echo htmlspecialchars($order['name']); ?>!
                <?php if ($order['status'] !== 'payment_failed' && $order['status'] !== 'cancelled'): ?>
                    We'll send you an update when your order ships.
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <!-- Order Summary Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order Summary #<?php echo $order_id; ?></h5>
                    <span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                while ($item = $order_items->fetch_assoc()):
                                    $subtotal = $item['quantity'] * $item['price'];
                                    $total += $subtotal;
                                ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                         class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['brand_name']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle"><?php echo $item['quantity']; ?></td>
                                        <td class="align-middle">₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="align-middle">₱<?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold">₱<?php echo number_format($total, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Next Steps</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo $next_steps; ?></p>
                    
                    <?php if ($order['status'] === 'payment_failed'): ?>
                        <div class="mt-3">
                            <a href="payment.php?retry=<?php echo $order_id; ?>" class="btn btn-primary">
                                <i class="fas fa-credit-card me-2"></i> Try Payment Again
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Info Sidebar -->
        <div class="col-lg-4">
            <!-- Order Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Order Date:</span>
                            <span class="text-muted"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Order Status:</span>
                            <span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Payment Method:</span>
                            <span class="text-muted">
                                <?php echo ucfirst($order['payment_method']) === 'Online' ? 'Online Payment' : 'Cash on Delivery'; ?>
                            </span>
                        </li>
                        <?php if ($payment): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Payment Status:</span>
                                <span class="badge <?php echo $payment['status'] === 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Shipping Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Shipping Details</h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong><?php echo htmlspecialchars($order['name']); ?></strong><br>
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                        <abbr title="Phone">P:</abbr> <?php echo htmlspecialchars($order['phone']); ?><br>
                        <abbr title="Email">E:</abbr> <?php echo htmlspecialchars($order['email']); ?>
                    </address>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="index.php" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-shopping-cart me-2"></i> Continue Shopping
                    </a>
                    <a href="#" onclick="window.print(); return false;" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-print me-2"></i> Print Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 