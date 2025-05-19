<?php
/**
 * Admin Dashboard
 * 
 * This page displays key statistics and information about the store
 * Including order counts, payment statistics, product inventory, and recent activity
 */
require_once '../config/database.php';
require_once 'includes/header.php';

// Get total orders count
$orders_query = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                    SUM(total_amount) as total_sales
                FROM orders";
$orders_result = $conn->query($orders_query);
$orders_stats = $orders_result->fetch_assoc();

// Get payment statistics
$payments_query = "SELECT 
                      COUNT(*) as total_payments,
                      COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_payments,
                      COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                      SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue
                  FROM payment_requests";
$payments_result = $conn->query($payments_query);
$payments_stats = $payments_result->fetch_assoc();

// Get inventory status
$inventory_query = "SELECT 
                       COUNT(*) as total_products,
                       COUNT(CASE WHEN stock = 0 THEN 1 END) as out_of_stock,
                       COUNT(CASE WHEN stock < 10 AND stock > 0 THEN 1 END) as low_stock,
                       AVG(stock) as avg_stock
                   FROM products";
$inventory_result = $conn->query($inventory_query);
$inventory_stats = $inventory_result->fetch_assoc();

// Get total users
$users_query = "SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0";
$users_result = $conn->query($users_query);
$users_stats = $users_result->fetch_assoc();

// Get recent orders
$recent_orders_query = "SELECT o.*, u.name as customer_name 
                        FROM orders o
                        JOIN users u ON o.user_id = u.id
                        ORDER BY o.created_at DESC
                        LIMIT 5";
$recent_orders = $conn->query($recent_orders_query);

// Get top selling products
$top_products_query = "SELECT p.id, p.name, p.price, p.image_url, 
                              SUM(oi.quantity) as total_sold,
                              (p.price * SUM(oi.quantity)) as revenue
                       FROM products p
                       JOIN order_items oi ON p.id = oi.product_id
                       JOIN orders o ON oi.order_id = o.id
                       WHERE o.status != 'cancelled'
                       GROUP BY p.id
                       ORDER BY total_sold DESC
                       LIMIT 5";
$top_products = $conn->query($top_products_query);
?>

<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Orders Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="stats-label">Total Orders</h6>
                        <h2 class="stats-number"><?php echo $orders_stats['total_orders'] ?? 0; ?></h2>
                    </div>
                    <div class="icon bg-primary-light text-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success me-1"><?php echo $orders_stats['completed_orders'] ?? 0; ?> Delivered</span>
                    <span class="badge bg-warning"><?php echo $orders_stats['pending_orders'] ?? 0; ?> Pending</span>
                </div>
            </div>
        </div>
        
        <!-- Revenue Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="stats-label">Total Revenue</h6>
                        <h2 class="stats-number">₱<?php echo number_format($payments_stats['total_revenue'] ?? 0, 2); ?></h2>
                    </div>
                    <div class="icon bg-success-light text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success me-1"><?php echo $payments_stats['successful_payments'] ?? 0; ?> Successful</span>
                    <span class="badge bg-danger"><?php echo $payments_stats['failed_payments'] ?? 0; ?> Failed</span>
                </div>
            </div>
        </div>
        
        <!-- Products Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="stats-label">Total Products</h6>
                        <h2 class="stats-number"><?php echo $inventory_stats['total_products'] ?? 0; ?></h2>
                    </div>
                    <div class="icon bg-info-light text-info">
                        <i class="fas fa-beer"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-danger me-1"><?php echo $inventory_stats['out_of_stock'] ?? 0; ?> Out of Stock</span>
                    <span class="badge bg-warning"><?php echo $inventory_stats['low_stock'] ?? 0; ?> Low Stock</span>
                </div>
            </div>
        </div>
        
        <!-- Users Stats -->
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="stats-label">Total Customers</h6>
                        <h2 class="stats-number"><?php echo $users_stats['total_users'] ?? 0; ?></h2>
                    </div>
                    <div class="icon bg-warning-light text-warning">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="users.php" class="btn btn-sm btn-outline-secondary">View All Users</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_orders->num_rows > 0): ?>
                                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
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
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No recent orders found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Selling Products -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Top Selling Products</h5>
                    <a href="products.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($top_products && $top_products->num_rows > 0): ?>
                                    <?php while ($product = $top_products->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($product['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                             class="me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </div>
                                            </td>
                                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo $product['total_sold']; ?></td>
                                            <td>₱<?php echo number_format($product['revenue'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No sales data available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="add_product.php" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-plus me-2"></i> Add New Product
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="manage_stock.php" class="btn btn-success w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-boxes me-2"></i> Manage Stock
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="orders.php?status=pending" class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-clock me-2"></i> Pending Orders
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="payments.php?status=created" class="btn btn-info w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-money-check me-2"></i> Pending Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 