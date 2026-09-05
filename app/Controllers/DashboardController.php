<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function index(): void
    {
        $medicineModel = new Medicine();
        $saleModel = new Sale();
        $settingModel = new Setting();

        $totalMedicines = $medicineModel->count(['is_active' => 1]);
        $lowStockCount = $medicineModel->countLowStock();
        $expiringCount = $medicineModel->countExpiring($settingModel->getInt('expiry_alert_days', 30));
        $totalSales = $saleModel->getTotalAmount();
        $recentSales = $saleModel->getRecent(5);

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
