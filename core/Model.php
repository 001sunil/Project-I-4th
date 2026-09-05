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
                $where[] = "`{$column}` = ?";
                $params[] = $value;
                $types .= is_int($value) ? 'i' : 's';
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
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
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
        $set = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($data)));
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
                $where[] = "`{$column}` = ?";
                $params[] = $value;
                $types .= is_int($value) ? 'i' : 's';
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
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_bool($value)) {
                $types .= 'i';
            } elseif ($value === null) {
                $types .= 's';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
}
