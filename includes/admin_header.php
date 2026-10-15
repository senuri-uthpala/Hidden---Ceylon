<?php
require_once __DIR__ . '/functions.php';
require_admin();
$flash     = get_flash();
$current   = basename($_SERVER['SCRIPT_NAME']);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$cssVer    = @filemtime(__DIR__ . '/../assets/css/admin.css') ?: 1;

// badge on the Enquiries link
$newCount = 0;
try {
    $newCount = (int)getDB()->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetchColumn();
} catch (Throwable $e) { /* sidebar must never break a page */ }

$navItems = [
    ['dashboard.php',    'Dashboard',    'fa-solid fa-house'],
    ['enquiries.php',    'Enquiries',    'fa-regular fa-rectangle-list'],
    ['destinations.php', 'Destinations', 'fa-solid fa-location-dot'],
    ['packages.php',     'Packages',     'fa-solid fa-suitcase-rolling'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(SITE_NAME) ?> — Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= $cssVer ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar" id="sidebar">
    <a href="dashboard.php" class="admin-logo" aria-label="Hidden Ceylon admin"><img src="../assets/images/logo.png?v=3" width="167" height="66" alt="Hidden Ceylon"></a>
    <nav class="admin-nav">
      <?php foreach ($navItems as [$file, $label, $icon]): ?>
        <a href="<?= $file ?>" class="<?= $current === $file ? 'active' : '' ?>"<?= $current === $file ? ' aria-current="page"' : '' ?>>
          <i class="<?= $icon ?>"></i> <?= $label ?>
          <?php if ($file === 'enquiries.php' && $newCount > 0): ?><span class="nav-count"><?= $newCount ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
      <div class="nav-sep"></div>
      <a href="administrators.php" class="<?= $current === 'administrators.php' ? 'active' : '' ?>"<?= $current === 'administrators.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-user-gear"></i> Administrator</a>
    </nav>
    <div class="admin-sidebar-foot">
      <a href="logout.php" class="admin-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </aside>
  <div class="scrim" id="scrim"></div>

  <div class="admin-content">
    <header class="topbar">
      <div class="topbar-left">
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        <a href="../index.php" class="view-site" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a>
      </div>
      <div class="user-menu" id="userMenu">
        <button class="user-btn" id="userBtn" aria-haspopup="true" aria-expanded="false">
          <span class="avatar"><i class="fa-solid fa-user"></i></span>
          <span class="user-name"><?= e($adminName) ?></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="user-dd">
          <a href="administrators.php"><i class="fa-solid fa-user-gear"></i> Administrators</a>
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log out</a>
        </div>
      </div>
    </header>
    <main class="admin-main">
    <?php if ($flash): ?>
      <div class="admin-flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
