<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$dbconnection = null;


function dbConnect() {

    $dbconnection = null;

    try {

        if ($dbconnection == null) {
            error_log('Opening connection');
            $dbconnection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_SERVERPORT);
            $GLOBALS['dbconnection'] = $dbconnection;
        }

        error_log('Connection open');

    }
    catch (EXCEPTION $err) {
        error_log('Err in opening db connection: ' . $err);
    }

    return $dbconnection;

}



function getSourceRows($sql): array {

    $dbconnection = $GLOBALS['dbconnection'];
    if (!$dbconnection) {
        // Open connection
        $dbconnection = dbConnect();
    }

    $rows = [];

    try {

        if ($dbconnection) {
            mysqli_set_charset($dbconnection, 'utf8mb4'); // important
            mysqli_query($dbconnection, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

            if ($res = mysqli_query($dbconnection, $sql)) {
                while ($r = mysqli_fetch_assoc($res)) {
                    $rows[] = $r;
                }
                mysqli_free_result($res);
            }

        }
    }
    catch (Exception $err) {
        error_log('Error in select: ' . $err);
    }

    return $rows;
}



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
