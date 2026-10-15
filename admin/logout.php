<?php
require_once __DIR__ . '/../includes/functions.php';
if (!empty($_SESSION['admin_id'])) {
    log_activity('Logged out');
}
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
