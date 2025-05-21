<?php
require_once 'includes/header.php';
require_once '../includes/db.php';

// Fetch all users
$users = [];
$result = $conn->query("SELECT id, username, email, is_admin FROM users ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<div class="admin-main flex-grow-1">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Users</h2>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info me-1 edit-user-btn" 
                                            data-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                            data-is_admin="<?php echo $user['is_admin']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="edit_user.php">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit-id" name="id">
        <div class="mb-3">
          <label for="edit-username" class="form-label">Username</label>
          <input type="text" class="form-control" id="edit-username" name="username" required>
        </div>
        <div class="mb-3">
          <label for="edit-email" class="form-label">Email</label>
          <input type="email" class="form-control" id="edit-email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="edit-password" class="form-label">Password (leave blank to keep current)</label>
          <input type="password" class="form-control" id="edit-password" name="password">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="edit-is-admin" name="is_admin">
          <label class="form-check-label" for="edit-is-admin">Admin</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
// Fill edit modal with user data
const editUserModal = document.getElementById('editUserModal');
editUserModal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  document.getElementById('edit-id').value = button.getAttribute('data-id');
  document.getElementById('edit-username').value = button.getAttribute('data-username');
  document.getElementById('edit-email').value = button.getAttribute('data-email');
  document.getElementById('edit-is-admin').checked = button.getAttribute('data-is_admin') == '1';
});
</script>

<?php require_once 'includes/footer.php'; ?> 