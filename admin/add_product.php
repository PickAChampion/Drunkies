<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';
require_once '../includes/upload_handler.php';
require_once '../includes/image_handler.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$db = Database::getInstance();
$imageHandler = new ImageHandler();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize($_POST['name']);
        $brand_id = (int)$_POST['brand_id'];
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $description = sanitize($_POST['description']);
        
        // Insert product with default image first
        $image_path = 'assets/images/no-image.svg';
        $stmt = $db->prepare("INSERT INTO products (name, brand_id, category_id, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siidsss", $name, $brand_id, $category_id, $price, $stock, $description, $image_path);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            // Handle image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $image_path = $imageHandler->handleProductImage($_FILES['image'], $product_id);
                } catch (Exception $e) {
                    // Log error but don't stop the process since product is already created
                    error_log("Error uploading image: " . $e->getMessage());
                    flashMessage("Product added successfully, but there was an error uploading the image.", "warning");
                    redirect('products.php');
                }
            }
            
            flashMessage("Product added successfully!");
            redirect('products.php');
        } else {
            throw new Exception("Error adding product to database");
        }
    } catch (Exception $e) {
        flashMessage($e->getMessage(), "danger");
    }
}

// Get brands and categories for dropdowns
$brands = $db->query("SELECT * FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add New Product</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="addProductForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select class="form-select" id="brand_id" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>">
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Maximum file size: 5MB. Allowed types: JPG, JPEG, PNG, GIF</div>
                            
                            <!-- Image preview -->
                            <div class="mt-2" id="imagePreview" style="display: none;">
                                <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Product</button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Image preview
    $('#image').change(function() {
        const file = this.files[0];
        const preview = $('#imagePreview');
        const previewImg = preview.find('img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.attr('src', e.target.result);
                preview.show();
            }
            reader.readAsDataURL(file);
        } else {
            preview.hide();
        }
    });
    
    // Form validation
    $('#addProductForm').submit(function(e) {
        const fileInput = $('#image')[0];
        const file = fileInput.files[0];
        
        if (file) {
            // Check file size (5MB)
            if (file.size > 5242880) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'File is too large. Maximum size is 5MB.',
                    icon: 'error'
                });
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Invalid file type. Allowed types: JPG, JPEG, PNG, GIF',
                    icon: 'error'
                });
                return;
            }
        }
        
        // Show loading state
        Swal.fire({
            title: 'Adding Product',
            text: 'Please wait...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 