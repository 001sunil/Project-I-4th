<?php
$pageTitle = 'All Medicines';
require __DIR__ . '/../layouts/header.php';
?>

<h1>All Medicines</h1>

<?php \Core\View::alert('success', $flashSuccess); ?>
<?php \Core\View::alert('danger', $flashDanger); ?>

<!-- Search & Filter Bar -->
<form method="GET" action="<?php echo \Core\View::url('/medicines'); ?>" class="search-bar">
    <input type="text" name="search" placeholder="Search by name or batch number..."
           value="<?php echo htmlspecialchars($search); ?>">
    <select name="category">
        <option value="0">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>"
                <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <a href="<?php echo \Core\View::url('/medicines'); ?>" class="btn btn-sm btn-warning">Clear</a>
</form>

<a href="<?php echo \Core\View::url('/medicines/create'); ?>" class="btn btn-success btn-mb">+ Add New Medicine</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Batch No</th>
                <th>Expiry</th>
                <th>Stock</th>
                <th>Unit</th>
                <th>Price (Rs)</th>
                <th>Rx</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($medicines) > 0): ?>
                <?php foreach ($medicines as $med): ?>
                <tr>
                    <td><?php echo (int) $med['id']; ?></td>
                    <td><?php echo htmlspecialchars($med['name']); ?></td>
                    <td><?php echo htmlspecialchars($med['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($med['batch_no']); ?></td>
                    <td><?php echo htmlspecialchars($med['expiry_date']); ?></td>
                    <td class="<?php echo $med['quantity'] < ($med['reorder_level'] ?? 10) ? 'stock-low' : ''; ?>">
                        <?php echo (int) $med['quantity']; ?>
                    </td>
                    <td><?php echo htmlspecialchars(ucfirst($med['unit'] ?? 'piece')); ?></td>
                    <td><?php echo number_format((float) $med['price'], 2); ?></td>
                    <td>
                        <?php if ($med['requires_prescription']): ?>
                            <span class="badge badge-required">Required</span>
                        <?php else: ?>
                            <span class="badge badge-not-required">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo \Core\View::url('/medicines/' . $med['id'] . '/edit'); ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <form method="POST" action="<?php echo \Core\View::url('/medicines/' . $med['id'] . '/delete'); ?>" style="display:inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars(addslashes($med['name'])); ?>')">
                            <?php \Core\View::csrfField(); ?>
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="table-empty">No medicines found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Build pagination params
$paginationParams = [];
if ($search !== '') $paginationParams['search'] = $search;
if ($categoryFilter > 0) $paginationParams['category'] = $categoryFilter;

// Simple pagination
if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($paginationParams, ['page' => $page - 1])); ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="?<?php echo http_build_query(array_merge($paginationParams, ['page' => $i])); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($paginationParams, ['page' => $page + 1])); ?>">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
