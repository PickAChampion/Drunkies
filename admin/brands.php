<?php
require_once '../config/database.php';
require_once 'includes/header.php';

// Function to generate a slug from a string
function generateSlug($string) {
    // Replace non letter or digits by -
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    // Transliterate
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    // Remove unwanted characters
    $string = preg_replace('~[^-\w]+~', '', $string);
    // Trim
    $string = trim($string, '-');
    // Remove duplicate -
    $string = preg_replace('~-+~', '-', $string);
    // Lowercase
    $string = strtolower($string);
    
    if (empty($string)) {
        return 'n-a';
    }
    
    return $string;
}

// Handle brand deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $brand_id = $_GET['delete'];
    
    // Check if brand has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE brand_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $error_message = "Cannot delete brand because it has associated products.";
    } else {
        $query = "DELETE FROM brands WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $brand_id);
        
        if ($stmt->execute()) {
            $success_message = "Brand deleted successfully.";
        } else {
            $error_message = "Failed to delete brand.";
        }
    }
}

// Handle form submission for adding/editing brand
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $brand_id = $_POST['brand_id'] ?? null;

    if (empty($name)) {
        $error_message = "Brand name is required.";
    } else {
        // Generate slug from name
        $slug = generateSlug($name);
        
        // Check if slug already exists
        $check_slug_query = "SELECT id FROM brands WHERE slug = ? AND id != ?";
        $stmt = $conn->prepare($check_slug_query);
        $brand_id_val = $brand_id ?: 0; // Create a variable to hold the value
        $stmt->bind_param("si", $slug, $brand_id_val);
        $stmt->execute();
        $slug_result = $stmt->get_result();
        
        if ($slug_result->num_rows > 0) {
            // Slug exists, append a random string
            $slug = $slug . '-' . substr(md5(time()), 0, 5);
        }
        
        if ($brand_id) {
            // Update existing brand
            $query = "UPDATE brands SET name = ?, description = ?, slug = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $name, $description, $slug, $brand_id);
        } else {
            // Add new brand
            $query = "INSERT INTO brands (name, description, slug) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $description, $slug);
        }

        if ($stmt->execute()) {
            $success_message = $brand_id ? "Brand updated successfully." : "Brand added successfully.";
            // Clear form data
            $name = $description = '';
            $brand_id = null;
        } else {
            $error_message = "Failed to save brand: " . $stmt->error;
        }
    }
}

// Get brand for editing
$edit_brand = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $brand_id = $_GET['edit'];
    $query = "SELECT * FROM brands WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_brand = $result->fetch_assoc();
        $name = $edit_brand['name'];
        $description = $edit_brand['description'];
        $brand_id = $edit_brand['id'];
    }
}

// Get all brands with product count
$query = "SELECT b.*, COUNT(p.id) as product_count 
          FROM brands b 
          LEFT JOIN products p ON b.id = p.brand_id 
          GROUP BY b.id 
          ORDER BY b.name";
$brands = $conn->query($query);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <?php echo $edit_brand ? 'Edit Brand' : 'Add New Brand'; ?>
                    </h5>
                </div>
                <div class="card-body">
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

                    <form action="" method="POST">
                        <?php if ($edit_brand): ?>
                            <input type="hidden" name="brand_id" value="<?php echo $edit_brand['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Brand Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo htmlspecialchars($description ?? ''); 
                            ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $edit_brand ? 'Update Brand' : 'Add Brand'; ?>
                            </button>
                            <?php if ($edit_brand): ?>
                                <a href="brands.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Brands</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($brand = $brands->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($brand['name']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($brand['slug'] ?? ''); ?></small></td>
                                    <td><?php echo htmlspecialchars($brand['description']); ?></td>
                                    <td><?php echo $brand['product_count']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?edit=<?php echo $brand['id']; ?>" 
                                               class="btn btn-sm btn-primary"
                                               data-bs-toggle="tooltip"
                                               title="Edit Brand">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($brand['product_count'] == 0): ?>
                                                <a href="?delete=<?php echo $brand['id']; ?>" 
                                                   class="btn btn-sm btn-danger delete-btn"
                                                   data-bs-toggle="tooltip"
                                                   title="Delete Brand">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 