<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Fetch featured brands
$query = "SELECT * FROM brands ORDER BY name ASC LIMIT 10";
$brands = $conn->query($query);

// Fetch categories with product counts
$query = "SELECT c.*, COUNT(p.id) as products_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id";
$categories = $conn->query($query);

// Fetch featured products
$query = "SELECT p.*, b.name as brand_name, b.slug as brand_slug,
          (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as rating,
          (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as reviews_count
          FROM products p 
          JOIN brands b ON p.brand_id = b.id 
          ORDER BY p.rating DESC, p.created_at DESC 
          LIMIT 8";
$featuredProducts = $conn->query($query);

// Fetch latest products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$query = "SELECT p.*, b.name as brand_name, b.slug as brand_slug,
          (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as rating
          FROM products p 
          JOIN brands b ON p.brand_id = b.id 
          ORDER BY p.created_at DESC 
          LIMIT $per_page OFFSET $offset";
$products = $conn->query($query);

// Get total products count for pagination
$query = "SELECT COUNT(*) as total FROM products";
$total_result = $conn->query($query);
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $per_page);
?>

<!-- Custom styles -->
<style>
    /* Top 10 Products Carousel Styles */
    .top10-carousel-container {
        position: relative;
        padding: 0 50px;
        margin-bottom: 2rem;
    }
    .products-carousel {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
        gap: 1rem;
        padding-bottom: 1rem;
    }
    .carousel-item {
        flex: 0 0 auto;
        width: 270px;
        transition: transform 0.3s ease;
        position: relative;
    }

    /* Product Card Styles */
    .product-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.08);
        transition: transform 0.3s ease;
        background: #fff;
        height: 100%;
        cursor: pointer;
    }
    .product-card:hover {
        transform: translateY(-5px) scale(1.03);
    }

    /* Carousel Navigation Controls */
    .carousel-control {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        z-index: 1;
        background: var(--beer-yellow);
        color: #222;
        border: none;
        font-size: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .carousel-control.prev { left: 10px; }
    .carousel-control.next { right: 10px; }

    /* Brand Carousel Styles */
    .brand-carousel-container {
        overflow: hidden;
        position: relative;
        width: 100%;
        background: #fff;
        padding: 2rem 0;
        margin: 2rem 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .brand-carousel {
        display: flex;
        animation: scrollBrands 30s linear infinite;
        width: calc(200px * 10);
    }
    .brand-carousel:hover {
        animation-play-state: paused;
    }
    .brand-logo {
        flex: 0 0 200px;
        height: 100px;
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        margin: 0 1rem;
        border-radius: 10px;
        transition: transform 0.3s ease;
    }
    .brand-logo:hover {
        transform: scale(1.05);
    }
    .brand-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        filter: grayscale(100%);
        transition: filter 0.3s ease;
    }
    .brand-logo:hover img {
        filter: grayscale(0%);
    }
    @keyframes scrollBrands {
        0% { transform: translateX(0); }
        100% { transform: translateX(calc(-200px * 5)); }
    }

    /* Product Card Styles */
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
        object-fit: contain;
        background: #fff;
        display: block;
        margin: 0 auto;
        padding: 10px;
        /* Prevent image from shrinking or growing */
        flex-shrink: 0;
        flex-grow: 0;
    }
    .product-details {
        padding: 1.5rem;
    }
    .product-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .product-brand {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    .product-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--ph-red);
        margin-bottom: 1rem;
    }
    .product-rating {
        color: #ffc107;
        margin-bottom: 1rem;
    }
    .product-actions {
        display: flex;
        gap: 1rem;
    }

    /* Hero Section */
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
    }

    .card.product-card, .product-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .card-body, .product-details {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
</style>

<!-- Hero Section -->
<div class="hero-section bg-dark text-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to Drunkies</h1>
                <p class="lead mb-4">Your premier destination for Philippine craft and commercial beers.</p>
                <a href="products.php" class="btn btn-warning btn-lg">Shop Now</a>
            </div>
            <div class="col-md-6">
                <img src="assets/images/hero-image.png" alt="Hero Image" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<!-- Featured Brands -->
<section class="brand-carousel-container">
    <div class="container">
        <h2 class="text-center mb-4">Featured Breweries</h2>
        <div class="brand-carousel">
            <?php while ($brand = $brands->fetch_assoc()): ?>
                <a href="brands.php?id=<?php echo $brand['id']; ?>" class="brand-logo">
                    <img src="<?php echo htmlspecialchars($brand['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($brand['name']); ?>" 
                         title="<?php echo htmlspecialchars($brand['name']); ?>">
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<div class="container py-5">
    <h2 class="text-center mb-4">Shop by Category</h2>
    <div class="row g-4">
        <?php while($category = $categories->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="categories.php?slug=<?php echo $category['slug']; ?>" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas <?php echo $category['icon']; ?> fa-2x mb-3 text-warning"></i>
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <small class="text-muted"><?php echo $category['products_count']; ?> Products</small>
                    </div>
                </div>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Featured Products Section -->
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Featured Products</h2>
        <a href="products.php" class="btn btn-outline-warning">View All</a>
    </div>
    <div class="row g-4">
        <?php while($product = $featuredProducts->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     class="product-image" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='assets/images/placeholder.jpg';">
                <div class="product-details">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                    <div class="product-rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $product['rating'] ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2 text-muted">(<?php echo $product['reviews_count'] ?? 0; ?> reviews)</span>
                    </div>
                    <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-actions">
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                class="btn btn-warning flex-grow-1">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                        <a href="products.php?brand=<?php echo $product['brand_slug']; ?>&product=<?php echo $product['slug']; ?>" 
                           class="btn btn-outline-dark">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Latest Products Section -->
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Latest Products</h2>
        <div class="d-flex align-items-center">
            <span id="product-count" class="me-3">
                Showing <?php echo $products->num_rows; ?> of <?php echo $total_products; ?> products
            </span>
        </div>
    </div>
    <div class="row g-4" id="products-container">
        <?php while($product = $products->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 product-card">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     class="card-img-top product-image" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='assets/images/placeholder.jpg';">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                    <div class="rating mb-2">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $product['rating'] ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="alcohol-content">
                        <i class="fas fa-wine-bottle me-1"></i>
                        <?php echo $product['alcohol_content']; ?>% ABV
                    </p>
                    <p class="product-price mb-3">₱<?php echo number_format($product['price'], 2); ?></p>
                    <div class="d-grid gap-2">
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                        </button>
                        <a href="products.php?brand=<?php echo $product['brand_slug']; ?>&product=<?php echo $product['slug']; ?>" 
                           class="btn btn-outline-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php if($page < $total_pages): ?>
    <div class="text-center mt-4">
        <button id="load-more" class="btn btn-warning btn-lg" 
                data-next-page="<?php echo $page + 1; ?>">
            Load More Products
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Toast for Cart Notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="cart-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Cart Update</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Infinite Scroll Implementation
    $('#load-more').click(function() {
        const button = $(this);
        const nextPage = button.data('next-page');
        
        // Show loading state
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
        
        // Fetch next page of products
        $.get('index.php?page=' + nextPage, function(response) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(response, 'text/html');
            const newProducts = doc.querySelectorAll('#products-container .col-6');
            
            // Append new products to container
            newProducts.forEach(product => {
                document.getElementById('products-container').appendChild(product);
            });
            
            // Update load more button state
            const hasMore = doc.querySelector('#load-more');
            if (hasMore) {
                button.data('next-page', nextPage + 1);
                button.prop('disabled', false).text('Load More Products');
            } else {
                button.remove();
            }
            
            // Update product count display
            const productCount = doc.getElementById('product-count').textContent;
            $('#product-count').text(productCount);
        });
    });
});

// Add to Cart Function
function addToCart(productId) {
    $.post('ajax/add_to_cart.php', {
        product_id: productId,
        quantity: 1
    })
    .done(function(response) {
        // Show success toast
        const toast = new bootstrap.Toast(document.getElementById('cart-toast'));
        $('#cart-toast .toast-body').text('Product added to cart successfully!');
        toast.show();

        // Optionally update cart count if your backend returns it
        // $('#cart-count').text(response.cartCount);
    })
    .fail(function(xhr) {
        // Show error toast
        const toast = new bootstrap.Toast(document.getElementById('cart-toast'));
        $('#cart-toast .toast-body').text(xhr.responseJSON?.message || 'Error adding product to cart');
        toast.show();
    });
}
</script>

<?php require_once 'includes/footer.php'; ?> 