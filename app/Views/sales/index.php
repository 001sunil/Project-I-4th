<?php
$pageTitle = 'Sales History';
require __DIR__ . '/../layouts/header.php';
?>

<h1>Sales History</h1>

<?php \Core\View::alert('success', $flashSuccess); ?>
<?php \Core\View::alert('danger', $flashDanger); ?>

<!-- Search Bar -->
<form method="GET" action="<?php echo \Core\View::url('/sales'); ?>" class="search-bar">
    <input type="text" name="search" placeholder="Search by medicine name or invoice number..."
           value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <a href="<?php echo \Core\View::url('/sales'); ?>" class="btn btn-sm btn-warning">Clear</a>
    <a href="<?php echo \Core\View::url('/sales/export'); ?>" class="btn btn-sm btn-success"><i class="fas fa-download"></i> Export CSV</a>
</form>

<a href="<?php echo \Core\View::url('/sales/create'); ?>" class="btn btn-success btn-mb">+ Record New Sale</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Sale #</th>
                <th>Medicine</th>
                <th>Qty Sold</th>
                <th>Unit Price (Rs)</th>
                <th>Total (Rs)</th>
                <th>Payment</th>
                <th>Sold By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sales) > 0): ?>
                <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($sale['sale_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($sale['medicine_name']); ?></td>
                    <td><?php echo $sale['quantity_sold']; ?></td>
                    <td><?php echo number_format($sale['sale_price'], 2); ?></td>
                    <td><strong><?php echo number_format($sale['total_amount'], 2); ?></strong></td>
                    <td><?php echo ucfirst($sale['payment_method']); ?></td>
                    <td><?php echo htmlspecialchars($sale['sold_by'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="table-empty">No sales recorded yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Simple pagination
if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $page - 1]); ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="?<?php echo http_build_query(['search' => $search, 'page' => $i]); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $page + 1]); ?>">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
