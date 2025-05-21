<?php
require_once 'includes/header.php';
require_once '../includes/db.php';

// Get current admin user
$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT username, email FROM users WHERE id = ?');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Handle flash messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<div class="admin-main flex-grow-1">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3 border-end">
                                <h5 class="mb-4">Settings</h5>
                                <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">Profile</button>
                                    <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">Security</button>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?php echo $success; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php echo $error; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                <div class="tab-content" id="settings-tabs-content">
                                    <!-- Profile Tab -->
                                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                        <h4 class="mb-4">Profile Settings</h4>
                                        <form method="post" action="update_profile.php">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Update Profile</button>
                                        </form>
                                    </div>
                                    <!-- Security Tab -->
                                    <div class="tab-pane fade" id="security" role="tabpanel">
                                        <h4 class="mb-4">Security Settings</h4>
                                        <form method="post" action="update_password.php">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Current Password</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Update Password</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Activate correct tab on click
const triggerTabList = [].slice.call(document.querySelectorAll('#settings-tabs button'));
triggerTabList.forEach(function (triggerEl) {
  triggerEl.addEventListener('click', function (event) {
    event.preventDefault();
    const tabTrigger = new bootstrap.Tab(triggerEl);
    tabTrigger.show();
  });
});
</script>
<?php require_once 'includes/footer.php'; ?> 