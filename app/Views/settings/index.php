<?php
$pageTitle = 'System Settings';
require __DIR__ . '/../layouts/header.php';
?>

<h1>System Settings</h1>

<?php \Core\View::alert('success', $flashSuccess); ?>
<?php \Core\View::alert('danger', $flashDanger); ?>

<div class="form-container">
    <form method="POST" action="<?php echo \Core\View::url('/settings/update'); ?>">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label for="low_stock_threshold">Low Stock Threshold</label>
            <input type="number" id="low_stock_threshold" name="low_stock_threshold"
                   value="<?php echo htmlspecialchars($settings['low_stock_threshold'] ?? '10'); ?>"
                   min="1" max="1000" required>
            <small style="color: var(--text-secondary);">Medicines with stock below this number will trigger low-stock alerts.</small>
        </div>

        <div class="form-group">
            <label for="expiry_alert_days">Expiry Alert Days</label>
            <input type="number" id="expiry_alert_days" name="expiry_alert_days"
                   value="<?php echo htmlspecialchars($settings['expiry_alert_days'] ?? '30'); ?>"
                   min="1" max="365" required>
            <small style="color: var(--text-secondary);">Alert for medicines expiring within this many days.</small>
        </div>

        <button type="submit" class="btn btn-success">Save Settings</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
