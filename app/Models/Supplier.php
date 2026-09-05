<?php

namespace App\Models;

use Core\Model;

class Supplier extends Model
{
    protected string $table = 'suppliers';

    /**
     * Get all active suppliers ordered by name (cached).
     */
    public function getAll(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = $this->db->fetchAll(
                "SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC"
            );
        }
        return $cache;
    }
}
