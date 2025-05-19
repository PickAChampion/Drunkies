<?php
/**
 * Main Footer
 * Displays the site footer including:
 * - Company information
 * - Quick links
 * - Contact information
 * - Social media links
 * - Copyright notice
 */
?>

<!-- Footer -->
<footer class="bg-dark text-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-light">Home</a></li>
                    <li><a href="products.php" class="text-light">Products</a></li>
                    <li><a href="brands.php" class="text-light">Brands</a></li>
                    <li><a href="categories.php" class="text-light">Categories</a></li>
                    <li><a href="about.php" class="text-light">About Us</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Categories</h5>
                <ul class="list-unstyled">
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="products.php?category=<?php echo $category['id']; ?>" class="text-light">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Featured Brands</h5>
                <ul class="list-unstyled">
                    <?php 
                    // Display first 5 brands
                    $brand_count = 0;
                    foreach ($brands as $brand): 
                        if ($brand_count >= 5) break;
                    ?>
                        <li>
                            <a href="products.php?brand=<?php echo $brand['id']; ?>" class="text-light">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </a>
                        </li>
                    <?php 
                        $brand_count++;
                    endforeach; 
                    ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-phone me-2"></i> +63 123 456 7890</li>
                    <li><i class="fas fa-envelope me-2"></i> info@drunkies.com</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> Cagayan de Oro City, Philippines</li>
                </ul>
                <div class="mt-3">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                </div>
            </div>
        </div>
        <hr class="mt-4 mb-3">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Drunkies. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="terms.php" class="text-light me-3">Terms of Service</a>
                <a href="privacy.php" class="text-light">Privacy Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom Scripts -->
<script src="assets/js/main.js"></script>
</body>
</html> 