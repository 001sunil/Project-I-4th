<?php
$pageTitle = 'New Sale';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Record New Sale</h1>

<div class="form-container">
    <?php \Core\View::alert('danger', $error); ?>

    <form method="POST" action="<?php echo \Core\View::url('/sales/store'); ?>">
        <?php \Core\View::csrfField(); ?>

        <div class="form-group">
            <label for="medicine_id">Select Medicine *</label>
            <select id="medicine_id" name="medicine_id" required onchange="onMedicineChange(this)">
                <option value="">— Select Medicine —</option>
                <?php foreach ($medicines as $med): ?>
                    <option value="<?php echo $med['id']; ?>"
                            data-price="<?php echo $med['price']; ?>"
                            data-stock="<?php echo $med['quantity']; ?>"
                            data-rx="<?php echo $med['requires_prescription']; ?>">
                        <?php echo htmlspecialchars($med['name']); ?> (Stock: <?php echo $med['quantity']; ?>, Rs. <?php echo number_format($med['price'], 2); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity_sold">Quantity *</label>
            <input type="number" id="quantity_sold" name="quantity_sold" min="1" value="1" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method *</label>
            <select id="payment_method" name="payment_method" required>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="digital">Digital Payment</option>
                <option value="credit">Credit</option>
            </select>
        </div>

        <div class="form-group" id="rx-fields" style="display:none;">
            <h3 style="margin-bottom: 12px;">Prescription Details</h3>

            <label for="doctor_name">Doctor Name *</label>
            <input type="text" id="doctor_name" name="doctor_name" placeholder="Dr. Name">

            <label for="nmc_number">NMC Number *</label>
            <input type="text" id="nmc_number" name="nmc_number" placeholder="NMC Registration No.">

            <label for="license_type">License Type</label>
            <select id="license_type" name="license_type">
                <option value="medical">Medical</option>
                <option value="dental">Dental</option>
                <option value="ayurveda">Ayurveda</option>
                <option value="other">Other</option>
            </select>

            <label for="hospital_name">Hospital / Clinic</label>
            <input type="text" id="hospital_name" name="hospital_name" placeholder="Hospital or clinic name">

            <label for="prescription_date">Prescription Date *</label>
            <input type="date" id="prescription_date" name="prescription_date">

            <label for="prescription_number">Prescription Number</label>
            <input type="text" id="prescription_number" name="prescription_number" placeholder="Optional reference">
        </div>

        <button type="submit" class="btn btn-success">Record Sale</button>
        <a href="<?php echo \Core\View::url('/sales'); ?>" class="btn btn-sm btn-warning">Cancel</a>
    </form>
</div>

<script>
function onMedicineChange(select) {
    var option = select.options[select.selectedIndex];
    var rxFields = document.getElementById('rx-fields');
    if (option.dataset.rx === '1') {
        rxFields.style.display = 'block';
    } else {
        rxFields.style.display = 'none';
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
