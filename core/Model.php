<?php

namespace Core;

/**
 * Base Model class.
 * All application models extend this.
 */
abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $allowedColumns = [];
    protected array $allowedDirections = ['ASC', 'DESC'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a record by primary key.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id],
            'i'
        );
    }

    /**
     * Find all records, optionally with conditions.
     */
    public function all(array $conditions = [], string $orderBy = '', string $direction = 'ASC'): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        $params = [];
        $types = '';

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                $where[] = "`{$column}` = ?";
                $params[] = $value;
                $types .= $this->buildType($value);
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if ($orderBy) {
            if (!empty($this->allowedColumns) && !in_array($orderBy, $this->allowedColumns, true)) {
                throw new \InvalidArgumentException("Invalid order by column: {$orderBy}");
            }
            $direction = strtoupper($direction);
            if (!in_array($direction, $this->allowedDirections, true)) {
                throw new \InvalidArgumentException("Invalid order direction: {$direction}");
            }
            $sql .= " ORDER BY `{$orderBy}` {$direction}";
        }

        return $this->db->fetchAll($sql, $params, $types);
    }

    /**
     * Insert a new record.
     */
    public function create(array $data): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Cannot create record with empty data.");
        }

        $sanitizedKeys = array_map(fn($col) => preg_replace('/[^a-zA-Z0-9_]/', '', $col), array_keys($data));
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", $sanitizedKeys));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $types = $this->buildTypes($data);

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, array_values($data), $types);

        return $this->db->lastInsertId();
    }

    /**
     * Update a record by primary key.
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $sanitizedKeys = array_map(fn($col) => preg_replace('/[^a-zA-Z0-9_]/', '', $col), array_keys($data));
        $set = implode(', ', array_map(fn($col) => "`{$col}` = ?", $sanitizedKeys));
        $types = $this->buildTypes($data);
        $types .= 'i'; // for the ID

        $sql = "UPDATE `{$this->table}` SET {$set} WHERE `{$this->primaryKey}` = ?";
        $this->db->query($sql, array_merge(array_values($data), [$id]), $types);

        return $this->db->affectedRows() > 0;
    }

    /**
     * Delete a record by primary key.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $this->db->query($sql, [$id], 'i');

        return $this->db->affectedRows() > 0;
    }

    /**
     * Count records, optionally with conditions.
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}`";
        $params = [];
        $types = '';

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                $where[] = "`{$column}` = ?";
                $params[] = $value;
                $types .= $this->buildType($value);
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->count($sql, $params, $types);
    }

    /**
     * Check if a record exists by conditions.
     */
    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    /**
     * Build type string for bind_param based on data values.
     */
    private function buildTypes(array $data): string
    {
        $types = '';
        foreach ($data as $value) {
            $types .= $this->buildType($value);
        }
        return $types;
    }

    /**
     * Build a single type character for bind_param.
     */
    private function buildType(mixed $value): string
    {
        if (is_int($value)) {
            return 'i';
        } elseif (is_float($value)) {
            return 'd';
        } elseif (is_bool($value)) {
            return 'i';
        } else {
            return 's';
        }
    }
}
