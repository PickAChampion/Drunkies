<?php
require_once '../config/database.php';
require_once 'includes/header.php';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Don't allow deleting own account
    if ($user_id == $_SESSION['user_id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        // Check if user has orders
        $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $error_message = "Cannot delete user because they have order history. Consider deactivating instead.";
        } else {
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success_message = "User deleted successfully.";
            } else {
                $error_message = "Failed to delete user.";
            }
        }
    }
}

// Handle toggle admin status
if (isset($_GET['toggle_admin']) && is_numeric($_GET['toggle_admin'])) {
    $user_id = $_GET['toggle_admin'];
    $current_status = $_GET['is_admin'];
    $new_status = $current_status ? 0 : 1;
    
    // Don't allow removing admin status from own account
    if ($user_id == $_SESSION['user_id'] && $current_status == 1) {
        $error_message = "You cannot remove admin privileges from your own account.";
    } else {
        $query = "UPDATE users SET is_admin = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $new_status, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User admin status updated successfully.";
        } else {
            $error_message = "Failed to update user status.";
        }
    }
}

// Handle edit user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    // Update user information
    $query = "UPDATE users SET name = ?, email = ?, address = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $name, $email, $address, $phone, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User information updated successfully.";
    } else {
        $error_message = "Failed to update user information.";
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $query = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User password reset successfully.";
    } else {
        $error_message = "Failed to reset user password.";
    }
}

// Get user for editing
$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $user_id = $_GET['edit'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
    }
}

// Get all users with order count
$query = "SELECT u.*, COUNT(o.id) as order_count 
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id 
          GROUP BY u.id 
          ORDER BY u.created_at DESC";
$users = $conn->query($query);
?>

<div class="container-fluid py-4">
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

    <div class="row">
        <?php if ($edit_user): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit User</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                        <input type="hidden" name="edit_user" value="1">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_user['name']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($edit_user['address']); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
                            </button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reset Password</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                        <input type="hidden" name="reset_password" value="1">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="<?php echo $edit_user ? 'col-md-8' : 'col-md-12'; ?>">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registered</th>
                                    <th>Orders</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['order_count']; ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="?toggle_admin=<?php echo $user['id']; ?>&is_admin=<?php echo $user['is_admin']; ?>" 
                                               class="btn btn-sm <?php echo $user['is_admin'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-success">Admin (You)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?edit=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-primary"
                                               data-bs-toggle="tooltip"
                                               title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id'] && $user['order_count'] == 0): ?>
                                                <a href="?delete=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-danger delete-btn"
                                                   data-bs-toggle="tooltip"
                                                   title="Delete User"
                                                   onclick="return confirm('Are you sure you want to delete this user?');">
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