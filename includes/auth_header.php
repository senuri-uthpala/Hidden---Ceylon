<?php
// Shared shell for the admin sign-in / registration screens.
// Set $authTitle before including this file.
require_once __DIR__ . '/functions.php';
$authTitle = $authTitle ?? 'Admin';
$cssVer    = @filemtime(__DIR__ . '/../assets/css/admin.css') ?: 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($authTitle) ?> — <?= e(SITE_NAME) ?></title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= $cssVer ?>">
</head>
<body class="auth-body">
<div class="auth-bg" aria-hidden="true"></div>
<div class="auth-deco" aria-hidden="true">
  <svg viewBox="0 0 420 220" fill="none">
    <path d="M10 205 L78 140 L108 168 L172 92 L236 178 L280 138 L350 205" stroke="#3FA9E8" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" opacity=".85"/>
    <path d="M150 112 L172 92 L190 112" stroke="#7FC8F5" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" opacity=".9"/>
    <path d="M205 120 C 270 118, 330 90, 372 44" stroke="#3FA9E8" stroke-width="2.4" stroke-dasharray="2 9" stroke-linecap="round" opacity=".9"/>
  </svg>
  <i class="fa-solid fa-plane"></i>
</div>
<main class="auth-wrap">
  <div class="auth-card">
    <a href="../index.php" class="auth-logo" aria-label="Hidden Ceylon — back to website"><img src="../assets/images/logo.png?v=3" width="192" height="76" alt="Hidden Ceylon"></a>
