<?php
// Remove item from cart if requested
if (isset($_GET['remove'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header('Location: cart.php');
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db.php';

// Ensure cart exists
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

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
?>
<div class="container py-5">
    <h2 class="mb-4">My Cart</h2>
    <?php if (empty($cart)): ?>
        <div class="alert alert-info">Your cart is empty. <a href="products.php">Browse products</a> to add some!</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart as $id => $item):
                    if (!isset($products[$id])) continue;
                    $product = $products[$id];
                    $line_total = $item['quantity'] * $product['price'];
                    $total += $line_total;
                ?>
                    <tr>
                        <td style="width:100px"><img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid product-image" style="height:80px;object-fit:contain;background:#fff;padding:8px;border-radius:8px;"></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                        <td><?php echo formatPrice($product['price']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatPrice($line_total); ?></td>
                        <td>
                            <a href="cart.php?remove=<?php echo $id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this product from your cart?');">
                                <i class="fas fa-trash-alt"></i> Remove
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Grand Total:</th>
                        <th><?php echo formatPrice($total); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-4">
            <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
            <a href="products.php" class="btn btn-secondary btn-lg ms-2">Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?> 