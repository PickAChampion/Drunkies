<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drunkies Admin - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Admin Dashboard CSS -->
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<div class="admin-sidebar">
    <div class="px-3 mb-4">
        <h4 class="text-center">Drunkies</h4>
        <p class="text-center mb-0"><small>Admin Panel</small></p>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
            <i class="fas fa-tachometer-alt me-2"></i>
            <span>Dashboard</span>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
            <i class="fas fa-beer me-2"></i>
            <span>Products</span>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'brands.php' ? 'active' : ''; ?>" href="brands.php">
            <i class="fas fa-building me-2"></i>
            <span>Brands</span>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
            <i class="fas fa-tags me-2"></i>
            <span>Categories</span>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
            <i class="fas fa-shopping-cart me-2"></i>
            <span>Orders</span>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
            <i class="fas fa-users me-2"></i>
            <span>Users</span>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
            <i class="fas fa-cog me-2"></i>
            <span>Settings</span>
        </a>
        <div class="dropdown-divider my-3"></div>
        <a class="nav-link" href="../index.php">
            <i class="fas fa-home me-2"></i>
            <span>View Site</span>
        </a>
        <a class="nav-link" href="../logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="admin-main">
    <!-- Header -->
    <div class="admin-header d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">
            <?php
            $page = basename($_SERVER['PHP_SELF'], '.php');
            echo ucfirst($page == 'index' ? 'Dashboard' : $page);
            ?>
        </h1>
        <div class="d-flex align-items-center">
            <span class="me-3"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container-fluid py-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 