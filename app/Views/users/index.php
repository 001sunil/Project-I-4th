<?php
$pageTitle = 'Manage Users';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Manage Users</h1>

<?php \Core\View::alert('success', $flashSuccess); ?>
<?php \Core\View::alert('danger', $flashDanger); ?>

<a href="<?php echo \Core\View::url('/users/create'); ?>" class="btn btn-success btn-mb">+ Add New User</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-required' : 'badge-not-required'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td>
                        <a href="<?php echo \Core\View::url('/users/' . $user['id'] . '/edit'); ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="POST" action="<?php echo \Core\View::url('/users/' . $user['id'] . '/delete'); ?>" style="display:inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars(addslashes($user['full_name'])); ?>')">
                            <?php \Core\View::csrfField(); ?>
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="table-empty">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
