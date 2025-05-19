<?php
/**
 * Admin Payments Page
 * 
 * This page allows administrators to view and manage payment transactions
 * It shows payment statuses, amounts, and order details
 */
require_once '../config/database.php';
require_once 'includes/header.php';

// Handle manual payment verification
if (isset($_GET['verify']) && is_numeric($_GET['verify'])) {
    $payment_id = $_GET['verify'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update payment request
        $update_payment = "UPDATE payment_requests SET 
                          status = 'completed', 
                          updated_at = NOW() 
                          WHERE id = ?";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        
        // Get order ID from payment request
        $query = "SELECT order_id FROM payment_requests WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $order_id = $row['order_id'];
        
        // Update order status
        $update_order = "UPDATE orders SET status = 'processing' WHERE id = ?";
        $stmt = $conn->prepare($update_order);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Payment verified successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error verifying payment: " . $e->getMessage();
    }
}

// Handle payment cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $payment_id = $_GET['cancel'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update payment request
        $update_payment = "UPDATE payment_requests SET 
                          status = 'failed', 
                          updated_at = NOW() 
                          WHERE id = ?";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        
        // Get order ID from payment request
        $query = "SELECT order_id FROM payment_requests WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $order_id = $row['order_id'];
        
        // Update order status
        $update_order = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($update_order);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Payment cancelled successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error cancelling payment: " . $e->getMessage();
    }
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$query = "SELECT pr.*, o.total_amount as order_total, o.status as order_status, 
                 u.name as customer_name, u.email as customer_email  
          FROM payment_requests pr
          JOIN orders o ON pr.order_id = o.id
          JOIN users u ON o.user_id = u.id";

$conditions = [];
$params = [];
$types = "";

// Add filters
if (!empty($status)) {
    $conditions[] = "pr.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($date_from)) {
    $conditions[] = "DATE(pr.created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $conditions[] = "DATE(pr.created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

// Add conditions to query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Order by most recent
$query .= " ORDER BY pr.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$payments = $stmt->get_result();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payment Transactions</h1>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter Payments</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Payment Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="created" <?php echo $status === 'created' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="payments.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payments->num_rows > 0): ?>
                            <?php while ($payment = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $payment['id']; ?></td>
                                    <td>
                                        <a href="view_order.php?id=<?php echo $payment['order_id']; ?>" class="text-decoration-none">
                                            #<?php echo $payment['order_id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($payment['customer_name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['customer_email']); ?></small>
                                    </td>
                                    <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <?php if (!empty($payment['payment_id'])): ?>
                                            <span class="text-muted"><?php echo $payment['payment_id']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = match($payment['status']) {
                                            'completed' => 'bg-success',
                                            'created' => 'bg-warning',
                                            'failed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_order.php?id=<?php echo $payment['order_id']; ?>" 
                                               class="btn btn-sm btn-info"
                                               data-bs-toggle="tooltip"
                                               title="View Order">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($payment['status'] === 'created'): ?>
                                                <a href="?verify=<?php echo $payment['id']; ?>" 
                                                   class="btn btn-sm btn-success"
                                                   data-bs-toggle="tooltip"
                                                   onclick="return confirm('Are you sure you want to verify this payment?');"
                                                   title="Verify Payment">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="?cancel=<?php echo $payment['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   data-bs-toggle="tooltip"
                                                   onclick="return confirm('Are you sure you want to cancel this payment?');"
                                                   title="Cancel Payment">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No payment transactions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Initialize tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 