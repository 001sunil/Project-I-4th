<?php
$pageTitle = 'Medicine Details';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Medicine Details</h1>

<div class="form-container">
    <div class="detail-row">
        <strong>Name:</strong>
        <?php echo htmlspecialchars($medicine['name']); ?>
    </div>
    <div class="detail-row">
        <strong>Category:</strong>
        <?php echo htmlspecialchars($medicine['category_name']); ?>
    </div>
    <div class="detail-row">
        <strong>Batch No:</strong>
        <?php echo htmlspecialchars($medicine['batch_no']); ?>
    </div>
    <div class="detail-row">
        <strong>Expiry Date:</strong>
        <?php echo htmlspecialchars($medicine['expiry_date']); ?>
    </div>
    <div class="detail-row">
        <strong>Stock:</strong>
        <?php echo (int) $medicine['quantity']; ?>
    </div>
    <div class="detail-row">
        <strong>Price:</strong>
        Rs. <?php echo number_format($medicine['price'], 2); ?>
    </div>
    <div class="detail-row">
        <strong>Prescription:</strong>
        <?php echo $medicine['requires_prescription'] ? 'Required' : 'Not Required'; ?>
    </div>

    <div style="margin-top: 20px;">
        <a href="<?php echo \Core\View::url('/medicines/' . $medicine['id'] . '/edit'); ?>" class="btn btn-primary">Edit</a>
        <a href="<?php echo \Core\View::url('/medicines'); ?>" class="btn btn-sm btn-warning">Back to List</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
