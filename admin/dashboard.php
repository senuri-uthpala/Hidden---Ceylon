<?php
require_once __DIR__ . '/../includes/admin_header.php';

$db = getDB();
$totalEnquiries = $db->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
$newEnquiries   = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetchColumn();
$totalDest      = $db->query('SELECT COUNT(*) FROM destinations WHERE is_active = 1')->fetchColumn();
$totalPkgs      = $db->query('SELECT COUNT(*) FROM packages WHERE is_active = 1')->fetchColumn();

$recent = $db->query('SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 6')->fetchAll();
?>
<div class="page-head">
  <div>
    <h1>Dashboard</h1>
    <p>Overview of enquiries and site content.</p>
  </div>
  <div class="date-chip"><i class="fa-regular fa-calendar"></i> <?= e(date('d M Y')) ?></div>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-ico"><i class="fa-solid fa-users"></i></div>
    <div class="label">Total Enquiries</div>
    <div class="value"><?= (int)$totalEnquiries ?></div>
    <div class="sub">All time</div>
  </div>
  <div class="stat-card">
    <div class="stat-ico"><i class="fa-regular fa-envelope"></i></div>
    <div class="label">New / Unread</div>
    <div class="value"><?= (int)$newEnquiries ?></div>
    <div class="sub">Needs a reply</div>
  </div>
  <div class="stat-card">
    <div class="stat-ico"><i class="fa-solid fa-location-dot"></i></div>
    <div class="label">Active Destinations</div>
    <div class="value"><?= (int)$totalDest ?></div>
    <div class="sub">Shown on site</div>
  </div>
  <div class="stat-card">
    <div class="stat-ico"><i class="fa-solid fa-briefcase"></i></div>
    <div class="label">Active Packages</div>
    <div class="value"><?= (int)$totalPkgs ?></div>
    <div class="sub">Shown on site</div>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h3>Recent Enquiries</h3>
    <a href="enquiries.php" class="btn btn-outline btn-sm">View All <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <?php if (empty($recent)): ?>
    <div class="empty-state"><i class="fa-solid fa-inbox"></i><div>No enquiries yet.</div></div>
  <?php else: ?>
    <table>
      <thead><tr><th>Name</th><th>Package</th><th>Date</th><th>Status</th><th>Received</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><strong><?= e($r['full_name']) ?></strong><br><span class="muted"><?= e($r['email']) ?></span></td>
          <td><?= e($r['package_interest'] ?: '—') ?></td>
          <td><?= $r['travel_date'] ? e(date('d M Y', strtotime($r['travel_date']))) : '—' ?></td>
          <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
          <td><?= e(date('d M Y, H:i', strtotime($r['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
