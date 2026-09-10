<?php
$pageTitle = 'New Stock Adjustment';
require __DIR__ . '/../layouts/header.php';
?>

<h1>New Stock Adjustment</h1>

<div class="form-container">
    <?php \Core\View::alert('danger', $error); ?>

    <form method="POST" action="<?php echo \Core\View::url('/stock-adjustments/store'); ?>" onsubmit="return validateAdjustmentForm();">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label for="medicine_id">Select Medicine *</label>
            <select id="medicine_id" name="medicine_id" required>
                <option value="">— Select Medicine —</option>
                <?php foreach ($medicines as $med): ?>
                    <option value="<?php echo (int) $med['id']; ?>">
                        <?php echo htmlspecialchars($med['name']); ?> (Stock: <?php echo (int) $med['quantity']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity_change">Quantity Change *</label>
            <input type="number" id="quantity_change" name="quantity_change" min="-9999" max="9999" placeholder="e.g. -5 or +10" required>
            <small style="color: var(--text-secondary);">Use negative to reduce stock, positive to increase stock.</small>
        </div>

        <div class="form-group">
            <label for="reason">Reason *</label>
            <select id="reason" name="reason" required>
                <option value="damaged">Damaged</option>
                <option value="expired">Expired</option>
                <option value="lost">Lost</option>
                <option value="returned">Returned</option>
                <option value="correction">Correction</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="note">Note</label>
            <textarea id="note" name="note" rows="3" placeholder="Optional details about the adjustment"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Record Adjustment</button>
        <a href="<?php echo \Core\View::url('/stock-adjustments'); ?>" class="btn btn-sm btn-warning">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
