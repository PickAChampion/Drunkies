<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure cart exists
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Get product details for all items in cart
$product_ids = array_keys($cart);
$products = [];
if (!empty($product_ids)) {
    $ids = implode(',', array_map('intval', $product_ids));
    $result = $conn->query("SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.id WHERE p.id IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

$total = 0;
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $payment_method = 'Mock Payment';
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$name || !$address || !$phone) {
        $error = 'Please fill in all shipping details.';
    } else {
        // Calculate total
        foreach ($cart as $id => $item) {
            if (!isset($products[$id])) continue;
            $product = $products[$id];
            $line_total = $item['quantity'] * $product['price'];
            $total += $line_total;
        }
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) VALUES (?, ?, 'processing', ?, ?)");
        $shipping_address = $name . "\n" . $address . "\n" . $phone;
        $stmt->bind_param("idss", $user_id, $total, $shipping_address, $payment_method);
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            // Insert order items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cart as $id => $item) {
                if (!isset($products[$id])) continue;
                $product = $products[$id];
                $item_stmt->bind_param("iiid", $order_id, $id, $item['quantity'], $product['price']);
                $item_stmt->execute();
            }
            unset($_SESSION['cart']);
            $success = true;
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}
?>
<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <h4 class="mb-2">Payment Successful!</h4>
            <p>Your order has been placed. Thank you for your purchase!</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="mb-4">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Shipping Address</label>
                    <input type="text" name="address" class="form-control" required value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
            </div>
            <h5 class="mb-3">Order Summary</h5>
            <div class="table-responsive mb-3">
                <table class="table align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $id => $item):
                            if (!isset($products[$id])) continue;
                            $product = $products[$id];
                            $line_total = $item['quantity'] * $product['price'];
                        ?>
                        <tr>
                            <td style="width:80px"><img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid product-image" style="height:60px;object-fit:contain;background:#fff;padding:6px;border-radius:8px;"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatPrice($line_total); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Grand Total:</th>
                            <th><?php echo formatPrice($total); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="submit" class="btn btn-success btn-lg">Pay Now</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?> 