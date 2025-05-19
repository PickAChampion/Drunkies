<?php
require_once 'includes/header.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$brand_id = isset($_GET['brand']) ? (int)$_GET['brand'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Build the query
$query = "SELECT p.*, b.name as brand_name, c.name as category_name,
          (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating
          FROM products p
          JOIN brands b ON p.brand_id = b.id
          JOIN categories c ON p.category_id = c.id
          WHERE 1=1";

if ($category_id) {
    $query .= " AND p.category_id = $category_id";
}
if ($brand_id) {
    $query .= " AND p.brand_id = $brand_id";
}
if ($search) {
    $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    case 'rating_desc':
        $query .= " ORDER BY avg_rating DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

$products = $conn->query($query);
?>

<style>
/* Product Card Image Styles */
.product-card {
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    overflow: hidden;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.2);
}
.product-image {
    height: 250px;
    width: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
}
.product-card:hover .product-image {
    transform: scale(1.05);
}
.product-rating {
    color: #ffc107;
    font-size: 1.1rem;
}
</style>

<div class="container py-5">
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="brand" class="form-select">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>" <?php echo $brand_id == $brand['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="sort" class="form-select">
                                <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                                <option value="rating_desc" <?php echo $sort == 'rating_desc' ? 'selected' : ''; ?>>Highest Rated</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 product-card">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="h5 mb-0"><?php echo formatPrice($product['price']); ?></span>
                            <div class="product-rating">
                                <?php
                                $rating = round($product['avg_rating'] ?? 0);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                        </div>
                        <p class="card-text small mb-3"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <div class="d-grid">
                            <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['product_id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle add to cart
    $('.add-to-cart').click(function() {
        const productId = $(this).data('product-id');
        $.post('ajax/add_to_cart.php', {
            product_id: productId,
            quantity: 1
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Product added to cart',
                    icon: 'success',
                    timer: 1500
                });
                // Update cart count in navbar
                if (response.cart_count) {
                    $('.cart-count').text(response.cart_count);
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to add product to cart',
                    icon: 'error'
                });
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 