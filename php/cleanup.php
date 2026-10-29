<?php
declare(strict_types=1);


/**
 * OAuth2-DB-Aufraeum-Job (per Cron aufrufen).
 *  Loescht abgelaufene Authorization Codes, Access- und Refresh Tokens.
 */


if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}


$globConfig = parse_ini_file(
    'dat'.DS.'configuration-'
        .strtolower(gethostname()).'-'.strtolower(php_uname('s')).'.ini',
    true
);


include_once 'inc'.DS.'database.inc.php';


// Abgelaufene Codes loeschen (sind ohnehin nur 60 s gueltig).
db()->exec('DELETE FROM oauth_auth_codes WHERE expires_at < NOW()');

// Abgelaufene Tokens loeschen.
// 7 Tage Puffer, damit abgelaufene/widerrufene Tokens ggf. noch auditiert werden koennen.
db()->exec('DELETE FROM oauth_access_tokens  WHERE expires_at < NOW() - INTERVAL 7 DAY');
db()->exec('DELETE FROM oauth_refresh_tokens WHERE expires_at < NOW() - INTERVAL 7 DAY');

// Optional: abgelaufene Session-Dateien entsorgen
$sessionDir = $globConfig['SESSION']['DIR'];
if ($sessionDir && is_dir($sessionDir)) {
    foreach (glob($sessionDir.DS.'sess_*') as $file) {
        if (filemtime($file) < time() - 7 * 86400) {
            @unlink($file);
        }
    }
}


echo "Cleanup OK\n";

