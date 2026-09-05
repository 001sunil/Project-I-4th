<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\PrescriptionDetail;

class SaleController extends Controller
{
    private Medicine $medicineModel;
    private Sale $saleModel;
    private PrescriptionDetail $prescriptionModel;

    public function __construct()
    {
        parent::__construct();
        $this->medicineModel = new Medicine();
        $this->saleModel = new Sale();
        $this->prescriptionModel = new PrescriptionDetail();
    }

    /**
     * List all sales with search and pagination.
     */
    public function index(): void
    {
        $search = trim($this->query('search', ''));
        $limit = 20;
        $page = max(1, (int) $this->query('page', 1));
        $offset = ($page - 1) * $limit;

        $totalRows = $this->saleModel->countFiltered($search);
        $totalPages = max(1, ceil($totalRows / $limit));

        $sales = $this->saleModel->getFiltered($search, $limit, $offset);

        $flashSuccess = $this->getFlash('success');
        $flashDanger = $this->getFlash('danger');

        require __DIR__ . '/../Views/sales/index.php';
    }

    /**
     * Show the create sale form.
     */
    public function create(): void
    {
        $medicines = $this->medicineModel->getActive();
        $error = $this->getFlash('danger');

        require __DIR__ . '/../Views/sales/create.php';
    }

    /**
     * Store a new sale.
     */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/sales/create');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect('/sales/create');
        }

        $medicineId = (int) $this->input('medicine_id', 0);
        $quantitySold = (int) $this->input('quantity_sold', 0);
        $paymentMethod = $this->input('payment_method', 'cash');

        $medicine = $this->medicineModel->find($medicineId);
        if (!$medicine) {
            $this->setFlash('danger', 'Medicine not found.');
            $this->redirect('/sales/create');
        }

        if ($quantitySold <= 0) {
            $this->setFlash('danger', 'Quantity must be greater than 0.');
            $this->redirect('/sales/create');
        }

        if ($quantitySold > $medicine['quantity']) {
            $this->setFlash('danger', 'Insufficient stock. Available: ' . $medicine['quantity']);
            $this->redirect('/sales/create');
        }

        $salePrice = $medicine['price'];
        $totalAmount = $quantitySold * $salePrice;
        $saleNumber = $this->saleModel->generateSaleNumber();
        $userId = (int) ($_SESSION['user_id'] ?? null);

        $this->db->beginTransaction();
        try {
            $saleId = $this->saleModel->create([
                'sale_number'     => $saleNumber,
                'medicine_id'     => $medicineId,
                'quantity_sold'   => $quantitySold,
                'sale_price'      => $salePrice,
                'total_amount'    => $totalAmount,
                'payment_method'  => $paymentMethod,
                'created_by'      => $userId,
            ]);

            $stockDecreased = $this->medicineModel->decreaseStock($medicineId, $quantitySold);
            if (!$stockDecreased) {
                throw new \RuntimeException('Failed to decrease stock. Insufficient quantity.');
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Failed to record sale: ' . $e->getMessage());
            $this->redirect('/sales/create');
        }

        if ($medicine['requires_prescription']) {
            $doctorName = trim($this->input('doctor_name', ''));
            $nmcNumber = trim($this->input('nmc_number', ''));
            $prescriptionDate = $this->input('prescription_date', '');
            $prescriptionNumber = trim($this->input('prescription_number', ''));
            $licenseType = $this->input('license_type', 'medical');
            $hospitalName = trim($this->input('hospital_name', ''));

            if (!empty($doctorName) && !empty($nmcNumber) && !empty($prescriptionDate)) {
                $this->prescriptionModel->createDetail(
                    $saleId,
                    $doctorName,
                    $nmcNumber,
                    $prescriptionDate,
                    $prescriptionNumber,
                    $licenseType,
                    $hospitalName
                );
            }
        }

        \Core\Logger::info('Sale recorded', ['sale_number' => $saleNumber, 'total' => $totalAmount]);
        \Core\AuditLog::log('sale_created', $userId, null, ['sale_number' => $saleNumber, 'total' => $totalAmount]);
        $this->setFlash('success', "Sale recorded! ({$saleNumber})");
        $this->redirect('/sales');
    }

    /**
     * Export sales to CSV.
     */
    public function export(): void
    {
        $sales = $this->saleModel->getFiltered('', 10000, 0);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Sale #', 'Medicine', 'Qty Sold', 'Unit Price', 'Total', 'Payment', 'Date', 'Sold By']);

        foreach ($sales as $sale) {
            fputcsv($output, [
                $sale['sale_number'],
                $sale['medicine_name'],
                $sale['quantity_sold'],
                $sale['sale_price'],
                $sale['total_amount'],
                $sale['payment_method'],
                $sale['sale_date'],
                $sale['sold_by'] ?? '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
