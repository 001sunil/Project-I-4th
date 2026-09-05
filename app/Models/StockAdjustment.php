<?php

namespace App\Models;

use Core\Model;

class StockAdjustment extends Model
{
    protected string $table = 'stock_adjustments';

    /**
     * Get all adjustments with medicine names and who adjusted them.
     */
    public function getAllWithMedicine(): array
    {
        return $this->db->fetchAll(
            "SELECT sa.*, m.name AS medicine_name, u.full_name AS adjusted_by_name
             FROM stock_adjustments sa
             JOIN medicines m ON sa.medicine_id = m.id
             LEFT JOIN users u ON sa.adjusted_by = u.id
             ORDER BY sa.adjusted_at DESC"
        );
    }

    /**
     * Create an adjustment record with the user who made it.
     */
    public function createAdjustment(int $medicineId, int $quantityChange, string $reason, string $note = '', ?int $userId = null): int
    {
        return $this->create([
            'medicine_id'     => $medicineId,
            'quantity_change' => $quantityChange,
            'reason'          => $reason,
            'note'            => $note,
            'adjusted_by'     => $userId,
            'adjusted_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
