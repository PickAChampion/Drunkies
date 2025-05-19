<?php
/**
 * Admin Categories Management
 * 
 * This page allows administrators to create, edit, and delete product categories
 */
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

// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    // Check if category has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $error_message = "Cannot delete category because it has associated products.";
    } else {
        $query = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $category_id);
        
        if ($stmt->execute()) {
            $success_message = "Category deleted successfully.";
        } else {
            $error_message = "Failed to delete category.";
        }
    }
}

// Handle form submission for adding/editing category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    if (empty($name)) {
        $error_message = "Category name is required.";
    } else {
        // Generate slug from name
        $slug = generateSlug($name);
        
        // Check if slug already exists
        $check_slug_query = "SELECT id FROM categories WHERE slug = ? AND id != ?";
        $stmt = $conn->prepare($check_slug_query);
        $category_id_val = $category_id ?: 0;
        $stmt->bind_param("si", $slug, $category_id_val);
        $stmt->execute();
        $slug_result = $stmt->get_result();
        
        if ($slug_result->num_rows > 0) {
            // Slug exists, append a random string
            $slug = $slug . '-' . substr(md5(time()), 0, 5);
        }
        
        if ($category_id) {
            // Update existing category
            $query = "UPDATE categories SET name = ?, description = ?, slug = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $name, $description, $slug, $category_id);
        } else {
            // Add new category
            $query = "INSERT INTO categories (name, description, slug) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $description, $slug);
        }

        if ($stmt->execute()) {
            $success_message = $category_id ? "Category updated successfully." : "Category added successfully.";
            // Clear form data
            $name = $description = '';
            $category_id = null;
        } else {
            $error_message = "Failed to save category: " . $stmt->error;
        }
    }
}

// Get category for editing
$edit_category = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $category_id = $_GET['edit'];
    $query = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_category = $result->fetch_assoc();
        $name = $edit_category['name'];
        $description = $edit_category['description'];
        $category_id = $edit_category['id'];
    }
}

// Get all categories with product count
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name";
$categories = $conn->query($query);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?>
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
                        <?php if ($edit_category): ?>
                            <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
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
                                <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                            </button>
                            <?php if ($edit_category): ?>
                                <a href="categories.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Categories</h5>
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
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($category['slug'] ?? ''); ?></small></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td><?php echo $category['product_count']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?edit=<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-primary"
                                               data-bs-toggle="tooltip"
                                               title="Edit Category">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($category['product_count'] == 0): ?>
                                                <a href="?delete=<?php echo $category['id']; ?>" 
                                                   class="btn btn-sm btn-danger delete-btn"
                                                   data-bs-toggle="tooltip"
                                                   title="Delete Category">
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