<?php

namespace App\Models;

use Core\Model;

class Supplier extends Model
{
    protected string $table = 'suppliers';
    private static ?array $staticCache = null;

    /**
     * Get all active suppliers ordered by name (cached).
     */
    public function getAll(): array
    {
        if (self::$staticCache === null) {
            self::$staticCache = $this->db->fetchAll(
                "SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC"
            );
        }
        return self::$staticCache;
    }

    /**
     * Invalidate the static cache (call after create/update/delete).
     */
    public static function invalidateCache(): void
    {
        self::$staticCache = null;
    }
}
