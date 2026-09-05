<?php

namespace Core;

/**
 * Singleton database connection wrapper.
 * Replaces the global $conn with a proper PDO or mysqli abstraction.
 */
class Database
{
    private static ?self $instance = null;
    private \mysqli $connection;

    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $this->connection = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );

        if ($this->connection->connect_error) {
            throw new \RuntimeException('Database connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');

        // Enable strict error reporting
        \mysqli_report(\MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT);
    }

    /**
     * Get the single Database instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the raw mysqli connection.
     */
    public function getConnection(): \mysqli
    {
        return $this->connection;
    }

    /**
     * Execute a prepared statement and return the result.
     */
    public function query(string $sql, array $params = [], string $types = ''): mixed
    {
        if (!empty($params)) {
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result instanceof \mysqli_result ? $result : true;
        }
        return $this->connection->query($sql);
    }

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    /**
     * Commit the current transaction.
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Roll back the current transaction.
     */
    public function rollBack(): void
    {
        $this->connection->rollback();
    }

    /**
     * Fetch all rows as associative arrays.
     */
    public function fetchAll(string $sql, array $params = [], string $types = ''): array
    {
        $result = $this->query($sql, $params, $types);
        return ($result && $result->num_rows > 0)
            ? $result->fetch_all(\MYSQLI_ASSOC)
            : [];
    }

    /**
     * Fetch a single row as associative array.
     */
    public function fetchOne(string $sql, array $params = [], string $types = ''): ?array
    {
        $result = $this->query($sql, $params, $types);
        return ($result && $result->num_rows > 0)
            ? $result->fetch_assoc()
            : null;
    }

    /**
     * Return a COUNT(*) value.
     */
    public function count(string $sql, array $params = [], string $types = ''): int
    {
        $row = $this->fetchOne($sql, $params, $types);
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Get the last inserted ID.
     */
    public function lastInsertId(): int
    {
        return (int) $this->connection->insert_id;
    }

    /**
     * Get the number of affected rows from the last query.
     */
    public function affectedRows(): int
    {
        return $this->connection->affected_rows;
    }

    /**
     * Escape a string for safe SQL output.
     */
    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup()
    {
        throw new \RuntimeException("Cannot unserialize singleton");
    }
}
