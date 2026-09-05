<?php

namespace App\Models;

use Core\Model;

class Sale extends Model
{
    protected string $table = 'sales';

    /**
     * Generate a unique sale number (e.g., INV-20260830-001).
     */
    public function generateSaleNumber(): string
    {
        $date = date('Ymd');
        $prefix = 'INV';

        $this->db->beginTransaction();
        try {
            $last = $this->db->fetchOne(
                "SELECT sale_number FROM sales WHERE sale_number LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE",
                ["{$prefix}-{$date}-%"],
                's'
            );

            if ($last) {
                $lastNum = (int) substr($last['sale_number'], -4);
                $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNum = '0001';
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            $nextNum = '0001';
        }

        return "{$prefix}-{$date}-{$nextNum}";
    }

    /**
     * Get sales with medicine details, with optional search and pagination.
     */
    public function getFiltered(string $search = '', int $limit = 20, int $offset = 0): array
    {
        $params = [];
        $types = '';
        $whereClause = '';

        if ($search !== '') {
            $whereClause = 'WHERE (m.name LIKE ? OR s.sale_number LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $types .= 'ss';
        }

        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        return $this->db->fetchAll(
            "SELECT s.*, m.name AS medicine_name, u.full_name AS sold_by
             FROM sales s
             JOIN medicines m ON s.medicine_id = m.id
             LEFT JOIN users u ON s.created_by = u.id
             {$whereClause}
             ORDER BY s.sale_date DESC
             LIMIT ? OFFSET ?",
            $params,
            $types
        );
    }

    /**
     * Count filtered sales (for pagination).
     */
    public function countFiltered(string $search = ''): int
    {
        $params = [];
        $types = '';
        $whereClause = '';

        if ($search !== '') {
            $whereClause = 'WHERE (m.name LIKE ? OR s.sale_number LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $types .= 'ss';
        }

        return $this->db->count(
            "SELECT COUNT(*) AS total
             FROM sales s
             JOIN medicines m ON s.medicine_id = m.id
             {$whereClause}",
            $params,
            $types
        );
    }

    /**
     * Get recent sales for the dashboard.
     */
    public function getRecent(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, m.name AS medicine_name
             FROM sales s
             JOIN medicines m ON s.medicine_id = m.id
             ORDER BY s.sale_date DESC
             LIMIT ?",
            [$limit],
            'i'
        );
    }

    /**
     * Get total sales amount.
     */
    public function getTotalAmount(): float
    {
        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) AS total FROM sales"
        );
        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Get prescription sales with doctor details.
     */
    public function getPrescriptionSales(): array
    {
        return $this->db->fetchAll(
            "SELECT s.id AS sale_id, s.sale_number, m.name AS medicine_name,
                    s.quantity_sold, s.total_amount, s.sale_date,
                    p.doctor_name, p.nmc_number, p.license_type,
                    p.prescription_date, p.prescription_number,
                    p.hospital_name
             FROM prescription_details p
             JOIN sales s ON p.sale_id = s.id
             JOIN medicines m ON s.medicine_id = m.id
             ORDER BY s.sale_date DESC"
        );
    }
}
