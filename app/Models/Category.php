<?php

namespace App\Models;

use Core\Model;

class Category extends Model
{
    protected string $table = 'categories';
    private static ?array $staticCache = null;

    /**
     * Get all active categories ordered by sort_order then name (cached).
     */
    public function getAll(): array
    {
        if (self::$staticCache === null) {
            self::$staticCache = $this->db->fetchAll(
                "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
            );
        }
        return self::$staticCache;
    }

    /**
     * Get category name by ID (cached).
     */
    public function getNameById(int $id): string
    {
        $rows = $this->getAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['id']] = $row['name'];
        }
        return $map[$id] ?? 'Unknown';
    }

    /**
     * Invalidate the static cache (call after create/update/delete).
     */
    public static function invalidateCache(): void
    {
        self::$staticCache = null;
    }
}
