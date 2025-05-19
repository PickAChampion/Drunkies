<?php
require_once 'includes/header.php';

// Get all categories with their product counts
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name";
$categories = $conn->query($query);

// Get featured products for each category
function getFeaturedProducts($category_id, $limit = 4) {
    global $conn;
    $query = "SELECT p.*, b.name as brand_name 
              FROM products p 
              JOIN brands b ON p.brand_id = b.id 
              WHERE p.category_id = ? 
              ORDER BY p.rating DESC 
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $category_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<div class="container py-5">
    <h1 class="mb-4">Product Categories</h1>
    
    <?php while ($category = $categories->fetch_assoc()): ?>
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">
                        <i class="fas <?php echo htmlspecialchars($category['icon']); ?> me-2"></i>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h2>
                    <span class="badge bg-primary"><?php echo $category['product_count']; ?> Products</span>
                </div>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                
                <h3 class="h6 mb-3">Featured Products in this Category:</h3>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php
                    $featured_products = getFeaturedProducts($category['id']);
                    while ($product = $featured_products->fetch_assoc()):
                    ?>
                        <div class="col">
                            <div class="card h-100 product-card">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h6 mb-0"><?php echo formatPrice($product['price']); ?></span>
                                        <div class="product-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $product['rating'] ? '★' : '☆';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">
                        View All <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 