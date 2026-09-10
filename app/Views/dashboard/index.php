<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Dashboard</h1>

<!-- Summary Cards -->
<div class="cards">
    <div class="card">
        <h3>Total Medicines</h3>
        <div class="number"><?php echo (int) $totalMedicines; ?></div>
    </div>
    <div class="card warning">
        <h3>Low Stock Items</h3>
        <div class="number"><?php echo (int) $lowStockCount; ?></div>
    </div>
    <div class="card danger">
        <h3>Expiring Soon</h3>
        <div class="number"><?php echo (int) $expiringCount; ?></div>
    </div>
    <div class="card success">
        <h3>Total Sales (Rs)</h3>
        <div class="number"><?php echo number_format((float) $totalSales, 2); ?></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="cards" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 32px;">
    <a href="<?php echo \Core\View::url('/medicines/create'); ?>" class="card" style="cursor:pointer; text-decoration:none;">
        <div style="font-size: 2rem; margin-bottom: 8px; color: var(--primary);"><i class="fas fa-plus-circle"></i></div>
        <h3>Add Medicine</h3>
    </a>
    <a href="<?php echo \Core\View::url('/sales/create'); ?>" class="card" style="cursor:pointer; text-decoration:none;">
        <div style="font-size: 2rem; margin-bottom: 8px; color: var(--success);"><i class="fas fa-shopping-cart"></i></div>
        <h3>New Sale</h3>
    </a>
    <a href="<?php echo \Core\View::url('/reports/low-stock'); ?>" class="card" style="cursor:pointer; text-decoration:none;">
        <div style="font-size: 2rem; margin-bottom: 8px; color: var(--warning);"><i class="fas fa-exclamation-triangle"></i></div>
        <h3>Low Stock</h3>
    </a>
    <a href="<?php echo \Core\View::url('/reports/expiry'); ?>" class="card" style="cursor:pointer; text-decoration:none;">
        <div style="font-size: 2rem; margin-bottom: 8px; color: var(--danger);"><i class="fas fa-clock"></i></div>
        <h3>Expiry Alerts</h3>
    </a>
</div>

<!-- Recent Sales -->
<?php if (count($recentSales) > 0): ?>
<div class="table-container" style="margin-bottom: 32px;">
    <div style="padding: 18px 20px 0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Recent Sales</h3>
        <a href="<?php echo \Core\View::url('/sales'); ?>" class="btn btn-sm btn-primary">View All</a>
    </div>
    <table style="margin-top: 12px;">
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Total (Rs)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentSales as $sale): ?>
            <tr>
                <td><?php echo htmlspecialchars($sale['medicine_name']); ?></td>
                <td><?php echo $sale['quantity_sold']; ?></td>
                <td><strong><?php echo number_format($sale['total_amount'], 2); ?></strong></td>
                <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
