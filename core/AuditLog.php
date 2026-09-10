<?php

namespace Core;

/**
 * Audit log helper — writes to the audit_log table.
 */
class AuditLog
{
    /**
     * Log an audit event to the database.
     */
    public static function log(
        string $action,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $entityType = '',
        ?int $entityId = null
    ): void {
        $db = Database::getInstance();

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $sql = "INSERT INTO audit_log (action, user_id, entity_type, entity_id, old_values, new_values, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $db->query($sql, [
            $action,
            $userId,
            $entityType,
            $entityId,
            $oldValues !== null ? json_encode($oldValues) : null,
            $newValues !== null ? json_encode($newValues) : null,
            $ipAddress,
        ], 'sisisss');
    }
}
