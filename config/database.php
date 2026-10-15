<?php
require_once __DIR__ . '/config.php';

/**
 * Work out the correct relative path back to setup.php from whichever
 * script is currently running (root scripts vs /admin/ scripts).
 */
function setup_url(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return (strpos($script, '/admin/') !== false) ? '../setup.php' : 'setup.php';
}

/** Friendly error screen instead of a blank page / raw PDO exception */
function db_error_page(string $detail): void
{
    $setup = setup_url();
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">'
        . '<title>Database not ready — ' . SITE_NAME . '</title>'
        . '<style>body{background:#071B30;color:#EAF2FB;font-family:system-ui,sans-serif;'
        . 'display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:24px}'
        . '.b{max-width:620px;background:#0E2C4C;border:1px solid rgba(234,242,251,.14);'
        . 'border-radius:14px;padding:32px 34px;line-height:1.65}'
        . 'h1{font-size:22px;margin:0 0 14px}code{background:#071B30;padding:2px 6px;border-radius:4px;font-size:13px}'
        . 'ol{padding-left:20px;margin:14px 0}a.btn{display:inline-block;margin-top:18px;background:#3FA9E8;color:#04182A;'
        . 'font-weight:600;padding:11px 20px;border-radius:8px;text-decoration:none}'
        . '.d{margin-top:16px;font-size:12.5px;color:#9FB6D3;word-break:break-word}</style></head><body><div class="b">'
        . '<h1>Database is not set up yet</h1>'
        . '<p>The site could not reach the <code>' . DB_NAME . '</code> database. Usually this means one of:</p>'
        . '<ol><li>MySQL is not running in WAMP/XAMPP (start the service first)</li>'
        . '<li><code>database/schema.sql</code> has not been imported yet</li>'
        . '<li>The username/password in <code>config/config.php</code> does not match your MySQL</li></ol>'
        . '<a class="btn" href="' . $setup . '">Open the setup checker &rarr;</a>'
        . '<div class="d">Details: ' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</div>'
        . '</div></body></html>';
    exit;
}

/** Build a PDO connection to a given host, optionally selecting a database */
function pdo_connect(string $host, ?string $dbName): PDO
{
    $dsn = 'mysql:host=' . $host . ';port=' . DB_PORT
         . ($dbName !== null ? ';dbname=' . $dbName : '') . ';charset=utf8mb4';
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

/** localhost <-> 127.0.0.1 fallback: on some stacks only one of the two works */
function db_hosts(): array
{
    $hosts = [DB_HOST];
    if (DB_HOST === 'localhost')      { $hosts[] = '127.0.0.1'; }
    elseif (DB_HOST === '127.0.0.1')  { $hosts[] = 'localhost'; }
    return $hosts;
}

/** PDO connection to the project database (singleton) */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $lastError = '';
        foreach (db_hosts() as $host) {
            try {
                $pdo = pdo_connect($host, DB_NAME);
                return $pdo;
            } catch (PDOException $e) {
                $lastError = $e->getMessage();
            }
        }
        // AJAX/JSON endpoints should not get an HTML page back
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || basename($_SERVER['SCRIPT_NAME'] ?? '') === 'process_booking.php') {
            http_response_code(503);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'The database is not available right now. Please run setup.php.']);
            exit;
        }
        db_error_page($lastError);
    }
    return $pdo;
}

/** Connection to the MySQL server WITHOUT selecting a database (used by setup.php) */
function getServerPDO(): PDO
{
    $lastError = null;
    foreach (db_hosts() as $host) {
        try {
            return pdo_connect($host, null);
        } catch (PDOException $e) {
            $lastError = $e;
        }
    }
    throw $lastError;
}
