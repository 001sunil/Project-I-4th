<?php

namespace App\Models;

use Core\Model;

class Medicine extends Model
{
    protected string $table = 'medicines';

    /**
     * Get medicines with category name, supporting search and filter.
     */
    public function getFiltered(string $search = '', int $categoryId = 0, int $limit = 10, int $offset = 0): array
    {
        $where = ["m.is_active = 1"];
        $params = [];
        $types = '';

        if ($search !== '') {
            $where[] = "(m.name LIKE ? OR m.batch_no LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $types .= 'ss';
        }

        if ($categoryId > 0) {
            $where[] = "m.category_id = ?";
            $params[] = $categoryId;
            $types .= 'i';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT m.*, c.name AS category_name, s.name AS supplier_name
                FROM medicines m
                JOIN categories c ON m.category_id = c.id
                JOIN suppliers s ON m.supplier_id = s.id
                {$whereClause}
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        return $this->db->fetchAll($sql, $params, $types);
    }

    /**
     * Count filtered medicines (for pagination).
     */
    public function countFiltered(string $search = '', int $categoryId = 0): int
    {
        $where = ["m.is_active = 1"];
        $params = [];
        $types = '';

        if ($search !== '') {
            $where[] = "(m.name LIKE ? OR m.batch_no LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $types .= 'ss';
        }

        if ($categoryId > 0) {
            $where[] = "m.category_id = ?";
            $params[] = $categoryId;
            $types .= 'i';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT COUNT(*) AS total
                FROM medicines m
                JOIN categories c ON m.category_id = c.id
                JOIN suppliers s ON m.supplier_id = s.id
                {$whereClause}";

        return $this->db->count($sql, $params, $types);
    }

    /**
     * Get a single medicine with its category and supplier names.
     */
    public function findWithCategory(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT m.*, c.name AS category_name, s.name AS supplier_name
             FROM medicines m
             JOIN categories c ON m.category_id = c.id
             JOIN suppliers s ON m.supplier_id = s.id
             WHERE m.id = ?",
            [$id],
            'i'
        );
    }

    /**
     * Get stock by category (for charts).
     */
    public function getStockByCategory(): array
    {
        return $this->db->fetchAll(
            "SELECT c.name AS category, SUM(m.quantity) AS total_stock
             FROM medicines m
             JOIN categories c ON m.category_id = c.id
             WHERE m.is_active = 1
             GROUP BY c.name"
        );
    }

    /**
     * Count low stock items using each medicine's own reorder_level.
     */
    public function countLowStock(): int
    {
        return $this->db->count(
            "SELECT COUNT(*) AS total FROM medicines
             WHERE is_active = 1 AND quantity < reorder_level"
        );
    }

    /**
     * Count expiring items (within X days, including already expired).
     */
    public function countExpiring(int $days): int
    {
        return $this->db->count(
            "SELECT COUNT(*) AS total FROM medicines
             WHERE is_active = 1 AND DATEDIFF(expiry_date, CURDATE()) <= ?",
            [$days],
            'i'
        );
    }

    /**
     * Get low stock medicines.
     */
    public function getLowStock(): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, c.name AS category_name
             FROM medicines m
             JOIN categories c ON m.category_id = c.id
             WHERE m.is_active = 1 AND m.quantity < m.reorder_level AND m.reorder_level > 0
             ORDER BY (m.quantity / m.reorder_level) ASC"
        );
    }

    /**
     * Get expiring medicines.
     */
    public function getExpiring(int $days): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, c.name AS category_name,
                    DATEDIFF(m.expiry_date, CURDATE()) AS days_remaining
             FROM medicines m
             JOIN categories c ON m.category_id = c.id
             WHERE m.is_active = 1 AND DATEDIFF(m.expiry_date, CURDATE()) <= ?
             ORDER BY m.expiry_date ASC",
            [$days],
            'i'
        );
    }

    /**
     * Decrease stock for a medicine.
     */
    public function decreaseStock(int $id, int $quantity): bool
    {
        $this->db->query(
            "UPDATE medicines SET quantity = quantity - ? WHERE id = ? AND quantity >= ?",
            [$quantity, $id, $quantity],
            'iii'
        );
        return $this->db->affectedRows() > 0;
    }

    /**
     * Adjust stock by a given amount (can be negative or positive).
     */
    public function adjustStock(int $id, int $change): bool
    {
        $this->db->query(
            "UPDATE medicines SET quantity = quantity + ? WHERE id = ? AND (quantity + ?) >= 0",
            [$change, $id, $change],
            'iii'
        );
        return $this->db->affectedRows() > 0;
    }

    /**
     * Get all active medicines (for dropdowns).
     */
    public function getActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM medicines WHERE is_active = 1 ORDER BY name ASC"
        );
    }
}
