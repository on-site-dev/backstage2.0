<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Returns a shared PDO connection to the widgets database, built from the
 * credentials in config.php. Reused by index.php (reading saved widgets)
 * and api/save_widget.php (writing them) so the connection logic lives
 * in exactly one place.
 */
function getWidgetsPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_SERVERPORT,
            DB_NAME,
            DB_CHARSET
        );

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}
