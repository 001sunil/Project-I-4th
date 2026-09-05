<?php
$pageTitle = 'Edit User';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Edit User</h1>

<div class="form-container">
    <?php \Core\View::alert('danger', $error); ?>

    <form method="POST" action="<?php echo \Core\View::url('/users/' . $user['id'] . '/update'); ?>" enctype="multipart/form-data">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label>Avatar</label>
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                <?php
                $avatarUrl = (new \App\Models\User())->getAvatarUrl($user['avatar'] ?? null);
                if ($avatarUrl): ?>
                    <img src="<?php echo $avatarUrl; ?>" alt="Avatar" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 600;">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)">
                    <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatar').click();">
                        <i class="fas fa-camera"></i> Change Photo
                    </button>
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">JPG, PNG. Max 2MB.</small>
                </div>
            </div>
            <div id="avatar-preview" style="display:none; margin-bottom: 12px;">
                <img id="preview-img" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
            </div>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            <small style="color: var(--text-secondary);">Username cannot be changed.</small>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="role">Role *</label>
            <select id="role" name="role" required>
                <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" minlength="6">
        </div>

        <button type="submit" class="btn btn-success">Update User</button>
        <a href="<?php echo \Core\View::url('/users'); ?>" class="btn btn-sm btn-warning">Cancel</a>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('avatar-preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
