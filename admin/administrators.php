<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$db   = getDB();
$meId = (int)$_SESSION['admin_id'];
$old  = ['full_name' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $fullName = clean($_POST['full_name'] ?? '');
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';
        $old      = ['full_name' => $fullName, 'username' => $username];
        $problem  = '';
        if ($fullName === '' || mb_strlen($fullName) > 100) { $problem = 'Please enter a full name (max 100 characters).'; }
        elseif ($m = validate_username($username))          { $problem = $m; }
        elseif ($m = validate_password($password))          { $problem = $m; }
        elseif ($password !== $confirm)                     { $problem = 'The two passwords do not match.'; }
        elseif (admin_username_taken($username))            { $problem = 'That username is already taken.'; }

        if ($problem) {
            flash('error', $problem);
        } else {
            $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash, full_name) VALUES (:u, :p, :n)');
            $stmt->execute(['u' => $username, 'p' => password_hash($password, PASSWORD_DEFAULT), 'n' => $fullName]);
            log_activity("Added administrator ($username)");
            flash('success', "Administrator “{$username}” added.");
            header('Location: administrators.php'); exit;
        }
    }

    if ($action === 'password') {
        $current = $_POST['current'] ?? '';
        $new     = $_POST['new'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $stmt = $db->prepare('SELECT password_hash FROM admin_users WHERE id = :id');
        $stmt->execute(['id' => $meId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current, $hash))  { flash('error', 'Your current password is not correct.'); }
        elseif ($m = validate_password($new))             { flash('error', $m); }
        elseif ($new !== $confirm)                        { flash('error', 'The two new passwords do not match.'); }
        else {
            $db->prepare('UPDATE admin_users SET password_hash = :p WHERE id = :id')
               ->execute(['p' => password_hash($new, PASSWORD_DEFAULT), 'id' => $meId]);
            session_regenerate_id(true);
            log_activity('Changed own password');
            flash('success', 'Password updated.');
            header('Location: administrators.php'); exit;
        }
    }

    if ($action === 'delete') {
        $id    = (int)($_POST['id'] ?? 0);
        $total = (int)$db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        if ($id === $meId)      { flash('error', 'You cannot delete the account you are logged in with.'); }
        elseif ($total <= 1)    { flash('error', 'At least one administrator must remain.'); }
        else {
            $db->prepare('DELETE FROM admin_users WHERE id = :id')->execute(['id' => $id]);
            log_activity("Deleted administrator #$id");
            flash('success', 'Administrator removed.');
        }
        header('Location: administrators.php'); exit;
    }
}

$admins = $db->query('SELECT id, username, full_name, created_at FROM admin_users ORDER BY id ASC')->fetchAll();
require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="page-head">
  <div>
    <h1>Administrators</h1>
    <p>People who can sign in to this admin panel.</p>
  </div>
</div>

<div class="two-col">
  <div class="card">
    <h3>Add administrator</h3>
    <form method="POST" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="form-group"><label>Full name</label><input type="text" name="full_name" required maxlength="100" value="<?= e($old['full_name']) ?>"></div>
      <div class="form-group"><label>Username</label><input type="text" name="username" required maxlength="30" value="<?= e($old['username']) ?>"></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required autocomplete="new-password"><div class="hint">At least 8 characters, with a letter and a number.</div></div>
      <div class="form-group"><label>Confirm password</label><input type="password" name="confirm" required autocomplete="new-password"></div>
      <button type="submit" class="btn btn-blue"><i class="fa-solid fa-user-plus"></i> Add administrator</button>
    </form>
  </div>

  <div class="card">
    <h3>Change my password</h3>
    <form method="POST" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="form-group"><label>Current password</label><input type="password" name="current" required autocomplete="current-password"></div>
      <div class="form-group"><label>New password</label><input type="password" name="new" required autocomplete="new-password"></div>
      <div class="form-group"><label>Confirm new password</label><input type="password" name="confirm" required autocomplete="new-password"></div>
      <button type="submit" class="btn btn-blue"><i class="fa-solid fa-key"></i> Update password</button>
    </form>
  </div>
</div>

<div class="card">
  <h3>All administrators (<?= count($admins) ?>)</h3>
  <table>
    <thead><tr><th>Name</th><th>Username</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($admins as $a): ?>
      <tr>
        <td><strong><?= e($a['full_name'] ?: '—') ?></strong><?php if ((int)$a['id'] === $meId): ?> <span class="badge you">You</span><?php endif; ?></td>
        <td><?= e($a['username']) ?></td>
        <td><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
        <td>
          <?php if ((int)$a['id'] !== $meId): ?>
          <form method="POST" onsubmit="return confirm('Remove this administrator?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
          </form>
          <?php else: ?><span class="muted">—</span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
