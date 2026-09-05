<?php

namespace Core;

/**
 * Simple file-based logger.
 */
class Logger
{
    private static string $logDir = '';

    /**
     * Set the log directory path.
     */
    public static function setLogDir(string $dir): void
    {
        self::$logDir = $dir;
    }

    /**
     * Log an error message.
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    /**
     * Log a warning message.
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    /**
     * Log an info message.
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    /**
     * Log a debug message.
     */
    public static function debug(string $message, array $context = []): void
    {
        self::write('DEBUG', $message, $context);
    }

    /**
     * Write a log entry to file.
     */
    private static function write(string $level, string $message, array $context = []): void
    {
        if (empty(self::$logDir)) {
            self::$logDir = __DIR__ . '/../logs';
        }

        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0755, true);
        }

        $logFile = self::$logDir . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userId = $_SESSION['user_id'] ?? '-';
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';

        $line = "[{$timestamp}] [{$level}] [IP:{$ip}] [User:{$userId}] {$message}{$contextStr}" . PHP_EOL;

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
