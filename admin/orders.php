<?php
/**
 * Admin Orders Page
 * 
 * This page allows administrators to view and manage orders
 * It provides filtering, sorting, and status update capabilities
 */
require_once '../config/database.php';
require_once 'includes/header.php';

// Handle order status update
if (isset($_GET['update_status']) && isset($_GET['order_id']) && isset($_GET['status'])) {
    $order_id = $_GET['order_id'];
    $status = $_GET['status'];
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $success_message = "Order status updated successfully.";
    } else {
        $error_message = "Failed to update order status.";
    }
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$customer = isset($_GET['customer']) ? $_GET['customer'] : '';

// Build query
$query = "SELECT o.*, u.name as username, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id";

$conditions = [];
$params = [];
$types = "";

// Add filters
if (!empty($status)) {
    $conditions[] = "o.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($date_from)) {
    $conditions[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $conditions[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

if (!empty($customer)) {
    $conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $search_term = "%" . $customer . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Add conditions to query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Order by most recent
$query .= " ORDER BY o.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Orders</h1>
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
            <h5 class="card-title mb-0">Filter Orders</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Order Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3">
                    <label for="customer" class="form-label">Customer</label>
                    <input type="text" class="form-control" id="customer" name="customer" value="<?php echo htmlspecialchars($customer); ?>" placeholder="Name or Email">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="orders.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['username']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst($order['payment_method']); ?></td>
                                    <td>
                                        <?php
                                        $badge_class = match($order['status']) {
                                            'pending' => 'bg-warning',
                                            'processing' => 'bg-info',
                                            'shipped' => 'bg-primary',
                                            'delivered' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="view_order.php?id=<?php echo $order['id']; ?>">
                                                        <i class="fas fa-eye me-2"></i> View Details
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li class="dropdown-header">Update Status</li>
                                                <?php if ($order['status'] != 'pending'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="?update_status=1&order_id=<?php echo $order['id']; ?>&status=pending">
                                                            <i class="fas fa-clock me-2"></i> Pending
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ($order['status'] != 'processing'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="?update_status=1&order_id=<?php echo $order['id']; ?>&status=processing">
                                                            <i class="fas fa-cogs me-2"></i> Processing
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ($order['status'] != 'shipped'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="?update_status=1&order_id=<?php echo $order['id']; ?>&status=shipped">
                                                            <i class="fas fa-truck me-2"></i> Shipped
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ($order['status'] != 'delivered'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="?update_status=1&order_id=<?php echo $order['id']; ?>&status=delivered">
                                                            <i class="fas fa-check-circle me-2"></i> Delivered
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ($order['status'] != 'cancelled'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="?update_status=1&order_id=<?php echo $order['id']; ?>&status=cancelled">
                                                            <i class="fas fa-times-circle me-2"></i> Cancelled
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No orders found.</td>
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