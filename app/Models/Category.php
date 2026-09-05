<?php

namespace App\Models;

use Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    /**
     * Get all active categories ordered by sort_order then name (cached).
     */
    public function getAll(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = $this->db->fetchAll(
                "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
            );
        }
        return $cache;
    }

    /**
     * Get category name by ID (cached).
     */
    public function getNameById(int $id): string
    {
        static $map = null;
        if ($map === null) {
            $rows = $this->getAll();
            $map = [];
            foreach ($rows as $row) {
                $map[$row['id']] = $row['name'];
            }
        }
        return $map[$id] ?? 'Unknown';
    }
}
