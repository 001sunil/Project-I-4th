<?php
$pageTitle = 'Edit Medicine';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Edit Medicine</h1>

<div class="form-container">
    <?php \Core\View::alert('danger', $error); ?>

    <form method="POST" action="<?php echo \Core\View::url('/medicines/' . $medicine['id'] . '/update'); ?>" onsubmit="return validateMedicineForm();">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Medicine Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($medicine['name']); ?>" maxlength="150" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php echo $medicine['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
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
                    <option value="<?php echo $sup['id']; ?>"
                        <?php echo $medicine['supplier_id'] == $sup['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sup['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="batch_no">Batch Number *</label>
            <input type="text" id="batch_no" name="batch_no" value="<?php echo htmlspecialchars($medicine['batch_no']); ?>" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="expiry_date">Expiry Date *</label>
            <input type="date" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($medicine['expiry_date']); ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="quantity">Quantity (Stock) *</label>
                <input type="number" id="quantity" name="quantity" min="0" value="<?php echo $medicine['quantity']; ?>" required>
            </div>

            <div class="form-group">
                <label for="unit">Unit *</label>
                <select id="unit" name="unit" required>
                    <?php foreach (['piece','tablet','capsule','bottle','tube','ml','strip','inhaler'] as $u): ?>
                        <option value="<?php echo $u; ?>" <?php echo ($medicine['unit'] ?? 'piece') === $u ? 'selected' : ''; ?>>
                            <?php echo ucfirst($u); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="price">Selling Price (Rs) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo $medicine['price']; ?>" required>
            </div>

            <div class="form-group">
                <label for="reorder_level">Reorder Level</label>
                <input type="number" id="reorder_level" name="reorder_level" min="0" value="<?php echo $medicine['reorder_level'] ?? 10; ?>">
                <small>Alert when stock falls below this number.</small>
            </div>
        </div>

        <div class="form-group">
            <input type="checkbox" id="requires_prescription" name="requires_prescription" value="1"
                <?php echo $medicine['requires_prescription'] ? 'checked' : ''; ?>>
            <label for="requires_prescription" class="checkbox-label">Requires Prescription</label>
        </div>

        <button type="submit" class="btn btn-success">Update Medicine</button>
        <a href="<?php echo \Core\View::url('/medicines'); ?>" class="btn btn-sm btn-warning">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
