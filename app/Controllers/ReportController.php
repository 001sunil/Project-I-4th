<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Setting;

class ReportController extends Controller
{
    /**
     * Low stock report (uses each medicine's own reorder_level).
     */
    public function lowStock(): void
    {
        Middleware::auth();

        $medicineModel = new Medicine();
        $lowStockMedicines = $medicineModel->getLowStock();

        require __DIR__ . '/../Views/reports/low_stock.php';
    }

    /**
     * Expiry alerts report.
     */
    public function expiryAlerts(): void
    {
        Middleware::auth();

        $settingModel = new Setting();
        $medicineModel = new Medicine();

        $days = $settingModel->getInt('expiry_alert_days', 30);
        $expiringMedicines = $medicineModel->getExpiring($days);

        require __DIR__ . '/../Views/reports/expiry_alerts.php';
    }

    /**
     * Prescription audit report.
     */
    public function prescriptionAudit(): void
    {
        Middleware::auth();

        $saleModel = new Sale();
        $prescriptionSales = $saleModel->getPrescriptionSales();

        require __DIR__ . '/../Views/reports/prescription_audit.php';
    }
}
