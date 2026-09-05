<?php
$pageTitle = 'Stock Adjustments';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Stock Adjustments</h1>

<?php \Core\View::alert('success', $flashSuccess); ?>
<?php \Core\View::alert('danger', $flashDanger); ?>

<a href="<?php echo \Core\View::url('/stock-adjustments/create'); ?>" class="btn btn-success btn-mb">+ New Adjustment</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Medicine</th>
                <th>Quantity Change</th>
                <th>Reason</th>
                <th>Note</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($adjustments) > 0): ?>
                <?php foreach ($adjustments as $adj): ?>
                <tr>
                    <td><?php echo $adj['id']; ?></td>
                    <td><?php echo htmlspecialchars($adj['medicine_name']); ?></td>
                    <td>
                        <strong style="color: <?php echo $adj['quantity_change'] < 0 ? 'var(--danger)' : 'var(--success)'; ?>">
                            <?php echo $adj['quantity_change']; ?>
                        </strong>
                    </td>
                    <td><?php echo ucfirst(htmlspecialchars($adj['reason'])); ?></td>
                    <td><?php echo htmlspecialchars($adj['note'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($adj['adjusted_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="table-empty">No stock adjustments recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
