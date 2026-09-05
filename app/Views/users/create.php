<?php
$pageTitle = 'Add User';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Add New User</h1>

<div class="form-container">
    <?php \Core\View::alert('danger', $error); ?>

    <form method="POST" action="<?php echo \Core\View::url('/users/store'); ?>" enctype="multipart/form-data">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label>Avatar</label>
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--border); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)">
                    <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatar').click();">
                        <i class="fas fa-camera"></i> Upload Photo
                    </button>
                    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">JPG, PNG. Max 2MB.</small>
                </div>
            </div>
            <div id="avatar-preview" style="display:none; margin-bottom: 12px;">
                <img id="preview-img" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
            </div>
        </div>

        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" placeholder="e.g. john_doe" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" placeholder="At least 6 characters" minlength="6" required>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" placeholder="e.g. John Doe" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="role">Role *</label>
            <select id="role" name="role" required>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Add User</button>
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
