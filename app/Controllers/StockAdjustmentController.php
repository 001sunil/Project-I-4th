<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Medicine;
use App\Models\StockAdjustment;

class StockAdjustmentController extends Controller
{
    private Medicine $medicineModel;
    private StockAdjustment $adjustmentModel;

    public function __construct()
    {
        parent::__construct();
        $this->medicineModel = new Medicine();
        $this->adjustmentModel = new StockAdjustment();
    }

    /**
     * List all stock adjustments.
     */
    public function index(): void
    {
        $adjustments = $this->adjustmentModel->getAllWithMedicine();
        $flashSuccess = $this->getFlash('success');
        $flashDanger = $this->getFlash('danger');

        require __DIR__ . '/../Views/reports/stock_adjustments.php';
    }

    /**
     * Show the create adjustment form.
     */
    public function create(): void
    {
        $medicines = $this->medicineModel->getActive();
        $error = $this->getFlash('danger');

        require __DIR__ . '/../Views/reports/stock_adjustment_form.php';
    }

    /**
     * Store a new stock adjustment.
     */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/stock-adjustments/create');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect('/stock-adjustments/create');
        }

        $medicineId = (int) $this->input('medicine_id', 0);
        $quantityChange = (int) $this->input('quantity_change', 0);
        $reason = $this->input('reason', 'other');
        $note = trim($this->input('note', ''));

        if ($medicineId <= 0) {
            $this->setFlash('danger', 'Please select a medicine.');
            $this->redirect('/stock-adjustments/create');
        }

        if ($quantityChange >= 0) {
            $this->setFlash('danger', 'Quantity must be a negative number for stock reduction.');
            $this->redirect('/stock-adjustments/create');
        }

        $medicine = $this->medicineModel->find($medicineId);
        if (!$medicine) {
            $this->setFlash('danger', 'Medicine not found.');
            $this->redirect('/stock-adjustments/create');
        }

        if (abs($quantityChange) > $medicine['quantity']) {
            $this->setFlash('danger', 'Adjustment exceeds available stock (' . $medicine['quantity'] . ').');
            $this->redirect('/stock-adjustments/create');
        }

        $this->db->beginTransaction();
        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $this->adjustmentModel->createAdjustment($medicineId, $quantityChange, $reason, $note, $userId);

            $stockUpdated = $this->medicineModel->adjustStock($medicineId, $quantityChange);
            if (!$stockUpdated) {
                throw new \RuntimeException('Failed to adjust stock.');
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Failed to record adjustment: ' . $e->getMessage());
            $this->redirect('/stock-adjustments/create');
        }

        \Core\Logger::info('Stock adjustment recorded', ['medicine_id' => $medicineId, 'change' => $quantityChange, 'reason' => $reason]);
        \Core\AuditLog::log('stock_adjustment', $userId, null, ['medicine_id' => $medicineId, 'change' => $quantityChange, 'reason' => $reason]);
        $this->setFlash('success', 'Stock adjustment recorded successfully.');
        $this->redirect('/stock-adjustments');
    }
}
