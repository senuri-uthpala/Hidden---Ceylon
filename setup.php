<?php
/**
 * Hidden Ceylon — setup & health check
 * Open http://localhost/hidden_ceylon/setup.php in the browser.
 * It checks the environment and can import database/schema.sql with one click.
 */
require_once __DIR__ . '/config/database.php';

$schemaPath = __DIR__ . '/database/schema.sql';
$installLog = [];
$installError = '';

/** Split a .sql file into statements, respecting quoted strings and comments */
function split_sql(string $sql): array
{
    $statements = [];
    $current    = '';
    $inSingle   = false;
    $inDouble   = false;
    $len        = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
        $c    = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';

        // line comments (-- or #) only outside strings
        if (!$inSingle && !$inDouble && (($c === '-' && $next === '-') || $c === '#')) {
            while ($i < $len && $sql[$i] !== "\n") { $i++; }
            continue;
        }
        if (!$inSingle && !$inDouble && $c === '/' && $next === '*') {
            $i += 2;
            while ($i < $len && !($sql[$i] === '*' && ($i + 1 < $len) && $sql[$i + 1] === '/')) { $i++; }
            $i++;
            continue;
        }

        if ($c === "'" && !$inDouble) { $inSingle = !$inSingle; }
        elseif ($c === '"' && !$inSingle) { $inDouble = !$inDouble; }

        if ($c === ';' && !$inSingle && !$inDouble) {
            $trimmed = trim($current);
            if ($trimmed !== '') { $statements[] = $trimmed; }
            $current = '';
            continue;
        }
        $current .= $c;
    }
    $trimmed = trim($current);
    if ($trimmed !== '') { $statements[] = $trimmed; }
    return $statements;
}

// ---------- Run the installer ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'install') {
    try {
        if (!is_readable($schemaPath)) {
            throw new RuntimeException('database/schema.sql is missing or not readable.');
        }
        $pdo = getServerPDO();
        $sql = file_get_contents($schemaPath);
        $count = 0;
        foreach (split_sql($sql) as $stmt) {
            $pdo->exec($stmt);
            $count++;
        }
        $installLog[] = "Executed $count SQL statements — database, tables and seed data are ready.";
    } catch (Throwable $e) {
        $installError = $e->getMessage();
    }
}

// ---------- Health checks ----------
$checks = [];
$phpOk  = version_compare(PHP_VERSION, '7.4.0', '>=');
$checks[] = ['PHP version', $phpOk, PHP_VERSION . ($phpOk ? '' : ' — PHP 7.4 or newer is required')];
$checks[] = ['PDO MySQL extension', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'loaded' : 'enable extension=pdo_mysql in php.ini'];
$checks[] = ['Sessions', session_status() === PHP_SESSION_ACTIVE, session_status() === PHP_SESSION_ACTIVE ? 'active' : 'session_start() failed'];

$serverOk = false; $dbOk = false; $tablesOk = false;
$serverMsg = ''; $dbMsg = ''; $tablesMsg = '';
$counts = [];

try {
    $srv = getServerPDO();
    $serverOk  = true;
    $serverMsg = 'connected to ' . DB_HOST . ':' . DB_PORT . ' as "' . DB_USER . '" (' . $srv->query('SELECT VERSION()')->fetchColumn() . ')';

    $exists = $srv->query("SHOW DATABASES LIKE " . $srv->quote(DB_NAME))->fetchColumn();
    $dbOk   = (bool)$exists;
    $dbMsg  = $dbOk ? DB_NAME . ' exists' : DB_NAME . ' does not exist yet — click "Install database" below';

    if ($dbOk) {
        $srv->exec('USE `' . DB_NAME . '`');
        $found  = $srv->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $needed = ['admin_users', 'destinations', 'packages', 'enquiries', 'activity_log'];
        $missing = array_diff($needed, $found);
        $tablesOk  = empty($missing);
        $tablesMsg = $tablesOk ? 'all 5 tables present' : 'missing: ' . implode(', ', $missing);
        if ($tablesOk) {
            foreach (['destinations', 'packages', 'enquiries', 'admin_users'] as $t) {
                $counts[$t] = (int)$srv->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            }
        }
    }
} catch (Throwable $e) {
    $serverMsg = $e->getMessage();
}

$checks[] = ['MySQL server', $serverOk, $serverMsg];
$checks[] = ['Database', $dbOk, $dbMsg];
$checks[] = ['Tables', $tablesOk, $tablesMsg];

$allGood = $phpOk && extension_loaded('pdo_mysql') && $serverOk && $dbOk && $tablesOk;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup — <?= htmlspecialchars(SITE_NAME) ?></title>
<style>
  body{background:#071B30;color:#EAF2FB;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;margin:0;padding:40px 20px;line-height:1.6;}
  .wrap{max-width:760px;margin:0 auto;}
  h1{font-size:26px;margin:0 0 6px;}
  .sub{color:#9FB6D3;margin-bottom:28px;font-size:14.5px;}
  .card{background:#0E2C4C;border:1px solid rgba(234,242,251,.14);border-radius:14px;padding:26px 28px;margin-bottom:20px;}
  .row{display:flex;gap:14px;padding:11px 0;border-bottom:1px solid rgba(234,242,251,.08);align-items:flex-start;}
  .row:last-child{border-bottom:0;}
  .dot{width:18px;height:18px;border-radius:50%;flex-shrink:0;margin-top:3px;font-size:11px;
       display:flex;align-items:center;justify-content:center;font-weight:700;color:#04182A;}
  .ok{background:#3FCB8C;} .bad{background:#E85D5D;}
  .name{width:180px;font-weight:600;flex-shrink:0;}
  .val{color:#B7CBE3;font-size:13.5px;word-break:break-word;}
  .btn{display:inline-block;background:#3FA9E8;color:#04182A;font-weight:600;padding:12px 22px;
       border:0;border-radius:9px;text-decoration:none;cursor:pointer;font-size:14.5px;font-family:inherit;}
  .btn.ghost{background:transparent;color:#EAF2FB;border:1px solid rgba(234,242,251,.3);margin-left:8px;}
  .note{background:rgba(63,203,140,.12);border:1px solid rgba(63,203,140,.35);color:#9EE8C6;
        padding:13px 16px;border-radius:9px;margin-bottom:18px;font-size:14px;}
  .err{background:rgba(232,93,93,.12);border:1px solid rgba(232,93,93,.35);color:#F3B4B4;
       padding:13px 16px;border-radius:9px;margin-bottom:18px;font-size:14px;}
  code{background:#071B30;padding:2px 6px;border-radius:4px;font-size:13px;}
  ul{margin:10px 0 0;padding-left:20px;color:#B7CBE3;font-size:14px;}
</style>
</head>
<body>
<div class="wrap">
  <h1>Hidden Ceylon — Setup</h1>
  <div class="sub">Environment check for the PHP + MySQL build. Delete this file before hosting publicly.</div>

  <?php foreach ($installLog as $line): ?><div class="note"><?= htmlspecialchars($line) ?></div><?php endforeach; ?>
  <?php if ($installError): ?><div class="err">Install failed: <?= htmlspecialchars($installError) ?></div><?php endif; ?>

  <div class="card">
    <?php foreach ($checks as [$name, $ok, $val]): ?>
      <div class="row">
        <div class="dot <?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✓' : '!' ?></div>
        <div class="name"><?= htmlspecialchars($name) ?></div>
        <div class="val"><?= htmlspecialchars((string)$val) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if ($counts): ?>
      <div class="row">
        <div class="dot ok">✓</div>
        <div class="name">Seed data</div>
        <div class="val"><?= (int)$counts['destinations'] ?> destinations · <?= (int)$counts['packages'] ?> packages ·
          <?= (int)$counts['enquiries'] ?> enquiries · <?= (int)$counts['admin_users'] ?> admin user(s)</div>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <?php if ($allGood): ?>
      <p><strong>Everything is ready.</strong> The site and admin panel should both work now.</p>
      <p style="margin-top:14px;">
        <a class="btn" href="index.php">Open the website</a>
        <a class="btn ghost" href="admin/login.php">Open the admin panel</a>
      </p>
      <p style="margin-top:14px;font-size:13.5px;color:#9FB6D3;">Admin login: <code>admin</code> / <code>Admin@123</code></p>
    <?php elseif ($serverOk): ?>
      <p>MySQL is reachable but the database is not ready. The button below imports
         <code>database/schema.sql</code> — it drops and recreates the project tables and reloads the seed data.</p>
      <form method="POST" style="margin-top:16px;"
            onsubmit="return confirm('This will drop and recreate the Hidden Ceylon tables. Continue?');">
        <input type="hidden" name="action" value="install">
        <button type="submit" class="btn">Install database now</button>
      </form>
    <?php else: ?>
      <p><strong>MySQL could not be reached.</strong> Check these, then reload this page:</p>
      <ul>
        <li>MySQL service is running in WAMP/XAMPP (tray icon green)</li>
        <li><code>config/config.php</code> — host <code><?= htmlspecialchars(DB_HOST) ?></code>,
            port <code><?= htmlspecialchars(DB_PORT) ?></code>, user <code><?= htmlspecialchars(DB_USER) ?></code>
            <?= DB_PASS === '' ? '(no password)' : '(password set)' ?></li>
        <li>MAMP users: the port is usually <code>8889</code>, not 3306</li>
      </ul>
    <?php endif; ?>
  </div>

  <div class="card">
    <strong>Opening the files directly won't work.</strong>
    <ul>
      <li>The folder must sit inside the web root — e.g. <code>C:\wamp64\www\hidden_ceylon</code> or <code>C:\xampp\htdocs\hidden_ceylon</code></li>
      <li>Always open it as <code>http://localhost/hidden_ceylon/index.php</code>, never by double-clicking <code>index.php</code>
          (a <code>file:///</code> URL shows raw PHP or a blank page)</li>
    </ul>
  </div>
</div>
</body>
</html>
