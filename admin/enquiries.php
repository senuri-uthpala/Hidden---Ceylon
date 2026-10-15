<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$db = getDB();

// Handle status update / delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && $id) {
        $status = in_array($_POST['status'] ?? '', ['new', 'contacted', 'closed'], true) ? $_POST['status'] : 'new';
        $stmt = $db->prepare('UPDATE enquiries SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
        log_activity("Updated enquiry #$id status to $status");
        flash('success', 'Enquiry status updated.');
    } elseif ($action === 'delete' && $id) {
        $stmt = $db->prepare('DELETE FROM enquiries WHERE id = :id');
        $stmt->execute(['id' => $id]);
        log_activity("Deleted enquiry #$id");
        flash('success', 'Enquiry deleted.');
    }
    header('Location: enquiries.php');
    exit;
}

$statusFilter = $_GET['status'] ?? 'all';
$sql = 'SELECT * FROM enquiries';
$params = [];
if (in_array($statusFilter, ['new', 'contacted', 'closed'], true)) {
    $sql .= ' WHERE status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="page-head">
  <div>
    <h1>Enquiries</h1>
    <p>Booking and contact form submissions from the public site.</p>
  </div>
  <div class="action-row">
    <a href="enquiries.php" class="btn btn-sm <?= $statusFilter === 'all' ? 'btn-blue' : 'btn-ghost' ?>">All</a>
    <a href="enquiries.php?status=new" class="btn btn-sm <?= $statusFilter === 'new' ? 'btn-blue' : 'btn-ghost' ?>">New</a>
    <a href="enquiries.php?status=contacted" class="btn btn-sm <?= $statusFilter === 'contacted' ? 'btn-blue' : 'btn-ghost' ?>">Contacted</a>
    <a href="enquiries.php?status=closed" class="btn btn-sm <?= $statusFilter === 'closed' ? 'btn-blue' : 'btn-ghost' ?>">Closed</a>
  </div>
</div>

<div class="card">
  <?php if (empty($enquiries)): ?>
    <div class="empty-state"><i class="fa-solid fa-inbox"></i><div>No enquiries found.</div></div>
  <?php else: ?>
    <table>
      <thead><tr><th>Contact</th><th>Trip</th><th>Message</th><th>Status</th><th>Received</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($enquiries as $r): ?>
        <tr>
          <td><strong><?= e($r['full_name']) ?></strong><br><span style="color:var(--paper-dim); font-size:12px;"><?= e($r['email']) ?></span></td>
          <td>
            <?= e($r['package_interest'] ?: 'Not specified') ?><br>
            <span style="color:var(--paper-dim); font-size:12px;">
              <?= $r['travel_date'] ? e(date('d M Y', strtotime($r['travel_date']))) : 'No date' ?> · <?= e($r['travelers'] ?: '—') ?>
            </span>
          </td>
          <td style="max-width:240px;"><?= e($r['message'] ?: '—') ?></td>
          <td>
            <form method="POST" style="display:flex; gap:6px; align-items:center;">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <select name="status" onchange="this.form.submit()" style="background:var(--ink); color:var(--paper); border:1px solid var(--line); border-radius:6px; padding:6px 8px; font-size:12.5px;">
                <option value="new" <?= $r['status'] === 'new' ? 'selected' : '' ?>>New</option>
                <option value="contacted" <?= $r['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                <option value="closed" <?= $r['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
              </select>
            </form>
          </td>
          <td><?= e(date('d M Y, H:i', strtotime($r['created_at']))) ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Delete this enquiry permanently?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
