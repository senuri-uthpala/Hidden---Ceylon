<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

function respond(bool $success, string $message): void
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Invalid request method.');
}

// CSRF check (manual — no die() here since this is an AJAX/JSON endpoint)
$submittedToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
    http_response_code(403);
    respond(false, 'Your session expired — please refresh the page and try again.');
}

$fullName = clean($_POST['fullName'] ?? '');
$email    = clean($_POST['email'] ?? '');
$date     = clean($_POST['travelDate'] ?? '');
$pax      = clean($_POST['travelers'] ?? '');
$pkg      = clean($_POST['packageInterest'] ?? '');
$message  = clean($_POST['message'] ?? '');

// Validation
$errors = [];
if ($fullName === '' || strlen($fullName) > 100) $errors[] = 'name';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = 'email';

$travelDate = null;
if ($date !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d && $d->format('Y-m-d') === $date) {
        $travelDate = $date;
    } else {
        $errors[] = 'date';
    }
}

if (!empty($errors)) {
    respond(false, 'Please check the highlighted fields (' . implode(', ', $errors) . ') and try again.');
}

try {
    $stmt = getDB()->prepare(
        'INSERT INTO enquiries (full_name, email, travel_date, travelers, package_interest, message, ip_address)
         VALUES (:full_name, :email, :travel_date, :travelers, :package_interest, :message, :ip)'
    );
    $stmt->execute([
        'full_name'        => $fullName,
        'email'            => $email,
        'travel_date'      => $travelDate,
        'travelers'        => $pax,
        'package_interest' => $pkg,
        'message'          => $message,
        'ip'               => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    respond(true, 'Thanks, ' . explode(' ', $fullName)[0] . ' — your enquiry has been received. Our team will email you shortly.');
} catch (Exception $e) {
    respond(false, 'Something went wrong saving your enquiry. Please try again in a moment.');
}
