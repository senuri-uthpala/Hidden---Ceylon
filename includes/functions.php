<?php
require_once __DIR__ . '/../config/database.php';

/** Escape output for safe HTML display (XSS protection) */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Trim + strip tags from raw input */
function clean(?string $value): string
{
    return trim(strip_tags($value ?? ''));
}

/** Generate (or reuse) a CSRF token for the current session */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Render a hidden CSRF input field */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted CSRF token, halting the request if invalid */
function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

/** Require an active admin session; redirect to login otherwise */
function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

/** Set a one-time flash message shown on the next page load */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Pop and clear the flash message, if any */
function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/** Record an admin action in the activity_log table */
function log_activity(string $action): void
{
    try {
        $stmt = getDB()->prepare(
            'INSERT INTO activity_log (admin_id, action) VALUES (:admin_id, :action)'
        );
        $stmt->execute([
            'admin_id' => $_SESSION['admin_id'] ?? null,
            'action'   => $action,
        ]);
    } catch (Exception $e) {
        // Logging failures should never break the request
    }
}

/** Username rules for admin accounts. Returns an error message, or '' when valid. */
function validate_username(string $u): string
{
    if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $u)) {
        return 'Username must be 3–30 characters: letters, numbers, dot, dash or underscore.';
    }
    return '';
}

/** Password rules for admin accounts. Returns an error message, or '' when valid. */
function validate_password(string $p): string
{
    if (strlen($p) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p)) {
        return 'Password must include at least one letter and one number.';
    }
    return '';
}

/** True if an admin account with this username already exists (case-insensitive). */
function admin_username_taken(string $username): bool
{
    $stmt = getDB()->prepare('SELECT id FROM admin_users WHERE LOWER(username) = LOWER(:u) LIMIT 1');
    $stmt->execute(['u' => $username]);
    return (bool)$stmt->fetch();
}



/**
 * One-time top-up: adds the extra destinations from database/extra_destinations.json
 * if they are missing, so older databases get all places without running SQL by hand.
 * A marker file stops it re-adding places an admin deletes later.
 */
function ensure_extra_destinations(PDO $db): void
{
    $marker = __DIR__ . '/../database/.extra_destinations_done';
    $file   = __DIR__ . '/../database/extra_destinations.json';
    if (is_file($marker) || !is_file($file)) return;
    try {
        $rows = json_decode((string)file_get_contents($file), true) ?: [];
        $exists = $db->prepare('SELECT COUNT(*) FROM destinations WHERE name = :n');
        $ins = $db->prepare('INSERT INTO destinations (name,region,tag,short_desc,long_desc,best_time,duration,highlight,icon,gradient,sort_order) VALUES (:name,:region,:tag,:sd,:ld,:bt,:du,:hl,:icon,:grad,:so)');
        foreach ($rows as $r) {
            $exists->execute([':n' => $r['name']]);
            if ((int)$exists->fetchColumn() > 0) continue;
            $ins->execute([':name'=>$r['name'], ':region'=>$r['region'], ':tag'=>$r['tag'], ':sd'=>$r['short_desc'],
                ':ld'=>$r['long_desc'], ':bt'=>$r['best_time'], ':du'=>$r['duration'], ':hl'=>$r['highlight'],
                ':icon'=>$r['icon'], ':grad'=>$r['gradient'], ':so'=>$r['sort_order']]);
        }
        @file_put_contents($marker, date('c'));
    } catch (Throwable $e) { /* never break the home page */ }
}
