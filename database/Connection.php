<?php

declare(strict_types=1);

namespace bs20php\database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton PDO connection wrapper.
 *
 * Configure via environment variables or a .env loader:
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_SCHEMA
 */
final class Connection
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createPdo();
        }

        return self::$instance;
    }

    private static function createPdo(): PDO
    {
        $host   = $_ENV['DB_HOST']   ?? 'localhost';
        $port   = $_ENV['DB_PORT']   ?? '5432';
        $dbName = $_ENV['DB_NAME']   ?? 'backstage';
        $user   = $_ENV['DB_USER']   ?? 'postgres';
        $pass   = $_ENV['DB_PASS']   ?? '';
        $schema = $_ENV['DB_SCHEMA'] ?? 'public';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Set search path to desired schema
            $pdo->exec("SET search_path TO {$schema}");

            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
