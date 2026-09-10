<?php
$pageTitle = 'Prescription Audit';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Prescription Audit</h1>
<p>All sales that required a prescription:</p>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Sale #</th>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Total (Rs)</th>
                <th>Date</th>
                <th>Doctor</th>
                <th>NMC No.</th>
                <th>License</th>
                <th>Hospital</th>
                <th>Rx Date</th>
                <th>Rx No.</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($prescriptionSales) > 0): ?>
                <?php foreach ($prescriptionSales as $sale): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($sale['sale_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($sale['medicine_name']); ?></td>
                    <td><?php echo $sale['quantity_sold']; ?></td>
                    <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                    <td><?php echo htmlspecialchars($sale['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($sale['nmc_number']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($sale['license_type'])); ?></td>
                    <td><?php echo htmlspecialchars($sale['hospital_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($sale['prescription_date']); ?></td>
                    <td><?php echo htmlspecialchars($sale['prescription_number'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="table-empty">No prescription sales recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
