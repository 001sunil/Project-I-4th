<?php
$pageTitle = 'Expiry Alerts';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Expiry Alerts</h1>
<p>Medicines expiring within <?php echo $days; ?> days (including already expired):</p>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Batch No</th>
                <th>Expiry Date</th>
                <th>Days Remaining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($expiringMedicines) > 0): ?>
                <?php foreach ($expiringMedicines as $med): ?>
                <tr>
                    <td><?php echo $med['id']; ?></td>
                    <td><?php echo htmlspecialchars($med['name']); ?></td>
                    <td><?php echo htmlspecialchars($med['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($med['batch_no']); ?></td>
                    <td><?php echo htmlspecialchars($med['expiry_date']); ?></td>
                    <td><strong><?php echo $med['days_remaining']; ?></strong></td>
                    <td>
                        <?php if ($med['days_remaining'] <= 0): ?>
                            <span class="badge badge-required">Expired</span>
                        <?php elseif ($med['days_remaining'] <= 7): ?>
                            <span class="badge badge-required">Critical</span>
                        <?php else: ?>
                            <span class="badge badge-not-required">Warning</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="table-empty">No medicines expiring soon!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
