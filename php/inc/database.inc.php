<?php
/**
 * php/inc/database.inc.php
 *
 * Open connection to database server and database.
 *
 * @author Harald Hemm <hemm@nexgo.de>
 * @copyright 2026 Harald Hemm Landstuhl (HaHeLa)
 * @license CC BY-NC-ND HaHeLa
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @global array $globConfig Globale Konfigurationsdaten. 
 */

function db(): \PDO
{
    global $globConfig;
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new \PDO(
            $globConfig['DATABASE']['DB_DSN'],
            $globConfig['DATABASE']['DB_USER'],
            $globConfig['DATABASE']['DB_PASS'], 
            [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

