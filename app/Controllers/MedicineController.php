<?php

namespace App\Controllers;

use Core\Controller;
use Core\View;
use App\Models\Medicine;
use App\Models\Category;
use App\Models\Supplier;

class MedicineController extends Controller
{
    private Medicine $medicineModel;
    private Category $categoryModel;
    private Supplier $supplierModel;

    public function __construct()
    {
        parent::__construct();
        $this->medicineModel = new Medicine();
        $this->categoryModel = new Category();
        $this->supplierModel = new Supplier();
    }

    /**
     * List all medicines with search, filter, and pagination.
     */
    public function index(): void
    {
        $search = trim($this->query('search', ''));
        $categoryFilter = (int) $this->query('category', 0);
        $limit = 10;
        $page = max(1, (int) $this->query('page', 1));
        $offset = ($page - 1) * $limit;

        $totalRows = $this->medicineModel->countFiltered($search, $categoryFilter);
        $totalPages = max(1, ceil($totalRows / $limit));

        $medicines = $this->medicineModel->getFiltered($search, $categoryFilter, $limit, $offset);
        $categories = $this->categoryModel->getAll();

        $flashSuccess = $this->getFlash('success');
        $flashDanger = $this->getFlash('danger');

        require __DIR__ . '/../Views/medicines/index.php';
    }

    /**
     * Show the create medicine form.
     */
    public function create(): void
    {
        $categories = $this->categoryModel->getAll();
        $suppliers = $this->supplierModel->getAll();
        $error = $this->getFlash('danger');

        require __DIR__ . '/../Views/medicines/create.php';
    }

    /**
     * Store a new medicine.
     */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/medicines/create');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect('/medicines/create');
        }

        $name = trim($this->input('name', ''));
        $categoryId = (int) $this->input('category_id', 0);
        $supplierId = (int) $this->input('supplier_id', 0);
        $batchNo = trim($this->input('batch_no', ''));
        $expiryDate = $this->input('expiry_date', '');
        $quantity = (int) $this->input('quantity', 0);
        $price = (float) $this->input('price', 0);
        $requiresPrescription = $this->input('requires_prescription') ? 1 : 0;
        $unit = trim($this->input('unit', 'piece'));
        $reorderLevel = (int) $this->input('reorder_level', 10);

        if (empty($name) || empty($batchNo) || empty($expiryDate)) {
            $this->setFlash('danger', 'Please fill in all required fields.');
            $this->redirect('/medicines/create');
        }

        if ($categoryId <= 0 || $supplierId <= 0) {
            $this->setFlash('danger', 'Please select a valid category and supplier.');
            $this->redirect('/medicines/create');
        }

        if ($quantity < 0) {
            $this->setFlash('danger', 'Quantity cannot be negative.');
            $this->redirect('/medicines/create');
        }

        if ($price <= 0) {
            $this->setFlash('danger', 'Price must be greater than 0.');
            $this->redirect('/medicines/create');
        }

        $id = $this->medicineModel->create([
            'name'                    => $name,
            'category_id'             => $categoryId,
            'supplier_id'             => $supplierId,
            'batch_no'                => $batchNo,
            'expiry_date'             => $expiryDate,
            'quantity'                => $quantity,
            'unit'                    => $unit,
            'price'                   => $price,
            'requires_prescription'   => $requiresPrescription,
            'reorder_level'           => $reorderLevel,
            'created_by'              => (int) ($_SESSION['user_id'] ?? null),
        ]);

        $this->setFlash('success', "Medicine added successfully! (ID: {$id})");
        $this->redirect('/medicines');
    }

    /**
     * Show a single medicine.
     */
    public function show(string $id): void
    {
        $medicine = $this->medicineModel->findWithCategory((int) $id);
        if (!$medicine) {
            $this->setFlash('danger', 'Medicine not found.');
            $this->redirect('/medicines');
        }

        require __DIR__ . '/../Views/medicines/show.php';
    }

    /**
     * Show the edit medicine form.
     */
    public function edit(string $id): void
    {
        $medicine = $this->medicineModel->find((int) $id);
        if (!$medicine) {
            $this->setFlash('danger', 'Medicine not found.');
            $this->redirect('/medicines');
        }

        $categories = $this->categoryModel->getAll();
        $suppliers = $this->supplierModel->getAll();
        $error = $this->getFlash('danger');

        require __DIR__ . '/../Views/medicines/edit.php';
    }

    /**
     * Update an existing medicine.
     */
    public function update(string $id): void
    {
        if (!$this->isPost()) {
            $this->redirect("/medicines/{$id}/edit");
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect("/medicines/{$id}/edit");
        }

        $name = trim($this->input('name', ''));
        $categoryId = (int) $this->input('category_id', 0);
        $supplierId = (int) $this->input('supplier_id', 0);
        $batchNo = trim($this->input('batch_no', ''));
        $expiryDate = $this->input('expiry_date', '');
        $quantity = (int) $this->input('quantity', 0);
        $price = (float) $this->input('price', 0);
        $requiresPrescription = $this->input('requires_prescription') ? 1 : 0;
        $unit = trim($this->input('unit', 'piece'));
        $reorderLevel = (int) $this->input('reorder_level', 10);

        if (empty($name) || empty($batchNo) || empty($expiryDate)) {
            $this->setFlash('danger', 'Please fill in all required fields.');
            $this->redirect("/medicines/{$id}/edit");
        }

        $this->medicineModel->update((int) $id, [
            'name'                    => $name,
            'category_id'             => $categoryId,
            'supplier_id'             => $supplierId,
            'batch_no'                => $batchNo,
            'expiry_date'             => $expiryDate,
            'quantity'                => $quantity,
            'unit'                    => $unit,
            'price'                   => $price,
            'requires_prescription'   => $requiresPrescription,
            'reorder_level'           => $reorderLevel,
            'updated_by'              => (int) ($_SESSION['user_id'] ?? null),
        ]);

        $this->setFlash('success', 'Medicine updated successfully!');
        $this->redirect('/medicines');
    }

    /**
     * Delete a medicine (soft delete - set is_active = 0).
     */
    public function destroy(string $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/medicines');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request.');
            $this->redirect('/medicines');
        }

        $this->medicineModel->update((int) $id, ['is_active' => 0]);
        $this->setFlash('success', 'Medicine removed from inventory.');
        $this->redirect('/medicines');
    }
}
