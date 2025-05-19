<?php
require_once '../config/database.php';
require_once 'includes/header.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Get product details
$query = "SELECT id, name, stock FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $quantity = (int)$_POST['quantity'] ?? 0;
    $reason = $_POST['reason'] ?? '';
    
    // Validate input
    $errors = [];
    if ($quantity <= 0) {
        $errors[] = "Quantity must be greater than 0.";
    }
    
    if (empty($reason)) {
        $errors[] = "Reason is required.";
    }
    
    if (empty($errors)) {
        $new_stock = $product['stock'];
        
        if ($action === 'add') {
            $new_stock += $quantity;
        } elseif ($action === 'subtract') {
            if ($quantity > $product['stock']) {
                $errors[] = "Cannot reduce stock below 0. Current stock: {$product['stock']}.";
            } else {
                $new_stock -= $quantity;
            }
        }
        
        if (empty($errors)) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update product stock
                $update_query = "UPDATE products SET stock = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $new_stock, $product_id);
                $stmt->execute();
                
                // Log stock adjustment
                $log_query = "INSERT INTO stock_adjustments (product_id, quantity, action, reason, adjusted_by, adjusted_at) 
                             VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($log_query);
                $stmt->bind_param("iissi", $product_id, $quantity, $action, $reason, $_SESSION['user_id']);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $success_message = "Stock successfully " . ($action === 'add' ? "increased" : "decreased") . " by $quantity units.";
                $product['stock'] = $new_stock; // Update the displayed stock value
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error_message = "Error updating stock: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Get recent stock adjustments for this product
$adjustments_query = "SELECT sa.*, u.username as adjusted_by_name 
                     FROM stock_adjustments sa 
                     JOIN users u ON sa.adjusted_by = u.id 
                     WHERE sa.product_id = ? 
                     ORDER BY sa.adjusted_at DESC 
                     LIMIT 10";
$stmt = $conn->prepare($adjustments_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$adjustments = $stmt->get_result();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manage Stock: <?php echo htmlspecialchars($product['name']); ?></h1>
        <div>
            <a href="edit_product.php?id=<?php echo $product_id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Product
            </a>
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
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

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Stock</h5>
                </div>
                <div class="card-body">
                    <h2 class="text-center"><?php echo $product['stock']; ?> units</h2>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Adjust Stock</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="action_add" value="add" checked>
                                    <label class="form-check-label" for="action_add">
                                        <i class="fas fa-plus-circle text-success"></i> Add Stock
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="action_subtract" value="subtract">
                                    <label class="form-check-label" for="action_subtract">
                                        <i class="fas fa-minus-circle text-danger"></i> Subtract Stock
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            <div class="form-text">e.g., "New shipment arrived", "Damaged goods", "Inventory correction"</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Apply Stock Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Stock Adjustments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>Adjusted By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($adjustments->num_rows > 0): ?>
                                    <?php while ($adjustment = $adjustments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($adjustment['adjusted_at'])); ?></td>
                                            <td>
                                                <?php if ($adjustment['action'] === 'add'): ?>
                                                    <span class="badge bg-success">Added</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Subtracted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $adjustment['quantity']; ?></td>
                                            <td><?php echo htmlspecialchars($adjustment['reason']); ?></td>
                                            <td><?php echo htmlspecialchars($adjustment['adjusted_by_name']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No stock adjustments yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 