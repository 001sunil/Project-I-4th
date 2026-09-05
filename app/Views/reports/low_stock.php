<?php
$pageTitle = 'Low Stock Report';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Low Stock Report</h1>
<p>Medicines below their reorder level:</p>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Batch No</th>
                <th>Current Stock</th>
                <th>Reorder Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($lowStockMedicines) > 0): ?>
                <?php foreach ($lowStockMedicines as $med): ?>
                <tr>
                    <td><?php echo $med['id']; ?></td>
                    <td><?php echo htmlspecialchars($med['name']); ?></td>
                    <td><?php echo htmlspecialchars($med['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($med['batch_no']); ?></td>
                    <td><strong><?php echo $med['quantity']; ?></strong></td>
                    <td><?php echo $med['reorder_level']; ?></td>
                    <td>
                        <?php if ($med['quantity'] == 0): ?>
                            <span class="badge badge-required">Out of Stock</span>
                        <?php else: ?>
                            <span class="badge badge-not-required">Low Stock</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="table-empty">All medicines are well-stocked!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
