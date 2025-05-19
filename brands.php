<?php
require_once 'includes/header.php';

// Get all brands with their product counts
$query = "SELECT b.*, COUNT(p.id) as product_count 
          FROM brands b 
          LEFT JOIN products p ON b.id = p.brand_id 
          GROUP BY b.id 
          ORDER BY b.name";
$brands = $conn->query($query);
?>

<div class="container py-5">
    <h1 class="mb-4">Our Brands</h1>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($brand = $brands->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="/Drunkies-Web-main/<?php echo htmlspecialchars($brand['image_url']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($brand['name']); ?>"
                         style="height: 200px; object-fit: contain; padding: 1rem;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($brand['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($brand['description']); ?></p>
                        <div class="mt-3">
                            <p class="mb-1"><strong>Founded:</strong> <?php echo $brand['founding_year']; ?></p>
                            <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($brand['headquarters']); ?></p>
                            <p class="mb-1"><strong>Products:</strong> <?php echo $brand['product_count']; ?> items</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="products.php?brand=<?php echo $brand['id']; ?>" class="btn btn-primary w-100">
                            View Products
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 