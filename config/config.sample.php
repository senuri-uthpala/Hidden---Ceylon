<?php
// ---------------------------------------------------------
// Hidden Ceylon — global configuration
// ---------------------------------------------------------

// Database credentials (adjust for your local WAMP/XAMPP setup)
define('DB_HOST', 'localhost');   // if this fails the code auto-retries 127.0.0.1
define('DB_PORT', '3306');        // MAMP users: change to 8889
define('DB_NAME', 'hidden_ceylon_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site
define('SITE_NAME', 'Hidden Ceylon');
define('BASE_URL', 'http://localhost/hidden_ceylon');

// Admin registration (admin/register.php) is protected by this invite code so
// strangers cannot create admin accounts. Share it only with people you trust,
// and change it. Set it to '' to switch public registration off completely.
define('ADMIN_INVITE_CODE', 'CHANGE-ME-BEFORE-USE');

// Sessions must be started before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic error visibility while developing — turn off in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Colombo');
