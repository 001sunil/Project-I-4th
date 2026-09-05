<?php
$pageTitle = 'Add Medicine';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Add New Medicine</h1>

<div class="form-container">
    <?php \Core\View::alert('danger', $error); ?>

    <form method="POST" action="<?php echo \Core\View::url('/medicines/store'); ?>" onsubmit="return validateMedicineForm();">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Medicine Name *</label>
            <input type="text" id="name" name="name" placeholder="e.g. Paracetamol 500mg" maxlength="150" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="supplier_id">Supplier *</label>
            <select id="supplier_id" name="supplier_id" required>
                <option value="">— Select Supplier —</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?php echo $sup['id']; ?>">
                        <?php echo htmlspecialchars($sup['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="batch_no">Batch Number *</label>
            <input type="text" id="batch_no" name="batch_no" placeholder="e.g. BATCH-001" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="expiry_date">Expiry Date *</label>
            <input type="date" id="expiry_date" name="expiry_date" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="quantity">Quantity (Stock) *</label>
                <input type="number" id="quantity" name="quantity" min="0" value="0" required>
            </div>

            <div class="form-group">
                <label for="unit">Unit *</label>
                <select id="unit" name="unit" required>
                    <option value="piece">Piece</option>
                    <option value="tablet">Tablet</option>
                    <option value="capsule">Capsule</option>
                    <option value="bottle">Bottle</option>
                    <option value="tube">Tube</option>
                    <option value="ml">ml</option>
                    <option value="strip">Strip</option>
                    <option value="inhaler">Inhaler</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="price">Selling Price (Rs) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" placeholder="e.g. 50.00" required>
            </div>

            <div class="form-group">
                <label for="reorder_level">Reorder Level</label>
                <input type="number" id="reorder_level" name="reorder_level" min="0" value="10">
                <small>Alert when stock falls below this number.</small>
            </div>
        </div>

        <div class="form-group">
            <input type="checkbox" id="requires_prescription" name="requires_prescription" value="1">
            <label for="requires_prescription" class="checkbox-label">Requires Prescription</label>
        </div>

        <button type="submit" class="btn btn-success">Add Medicine</button>
        <a href="<?php echo \Core\View::url('/medicines'); ?>" class="btn btn-sm btn-warning">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
