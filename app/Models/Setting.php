<?php

namespace App\Models;

use Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    private static ?array $cache = null;

    /**
     * Get all settings as key => value array (cached for request).
     */
    public function getAll(): array
    {
        if (self::$cache === null) {
            $rows = $this->db->fetchAll(
                "SELECT setting_key, setting_value FROM settings"
            );
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    /**
     * Get a single setting value by key.
     */
    public function get(string $key): ?string
    {
        $settings = $this->getAll();
        return $settings[$key] ?? null;
    }

    /**
     * Get a setting as integer.
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int) ($this->get($key) ?? $default);
    }

    /**
     * Update a setting value and invalidate cache.
     */
    public function set(string $key, string $value): void
    {
        $existing = $this->db->fetchOne(
            "SELECT id FROM settings WHERE setting_key = ?",
            [$key],
            's'
        );

        if ($existing) {
            $this->db->query(
                "UPDATE settings SET setting_value = ? WHERE setting_key = ?",
                [$value, $key],
                'ss'
            );
        } else {
            $this->db->query(
                "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)",
                [$key, $value],
                'ss'
            );
        }

        // Invalidate the cache so subsequent reads get the updated value
        self::$cache = null;
    }
}
