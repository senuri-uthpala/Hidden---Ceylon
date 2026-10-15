<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$db = getDB();
$editing = null;

/** Save an uploaded destination photo. Returns the web path, or null if nothing valid was uploaded. */
function save_destination_image(array $file, string $name): ?string
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Image upload failed (error code ' . (int)$file['error'] . ').');
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        flash('error', 'Image is too large — max 5 MB.');
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || !@getimagesize($file['tmp_name'])) {
        flash('error', 'Only JPG, PNG or WEBP images are allowed.');
        return null;
    }
    $dir = __DIR__ . '/../assets/images/destinations/';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')) ?: 'destination';
    $filename = $slug . '-' . time() . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        flash('error', 'Could not save the uploaded image. Check folder permissions on assets/images/destinations/.');
        return null;
    }
    return 'assets/images/destinations/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM destinations WHERE id = :id')->execute(['id' => $id]);
        log_activity("Deleted destination #$id");
        flash('success', 'Destination deleted.');
        header('Location: destinations.php'); exit;
    }

    if ($action === 'save') {
        $id        = (int)($_POST['id'] ?? 0);
        $name      = clean($_POST['name'] ?? '');
        $region    = clean($_POST['region'] ?? '');
        $tag       = clean($_POST['tag'] ?? '');
        $shortDesc = clean($_POST['short_desc'] ?? '');
        $longDesc  = clean($_POST['long_desc'] ?? '');
        $bestTime  = clean($_POST['best_time'] ?? '');
        $duration  = clean($_POST['duration'] ?? '');
        $highlight = clean($_POST['highlight'] ?? '');
        $icon      = clean($_POST['icon'] ?? 'fa-map-pin');
        $gradient  = clean($_POST['gradient'] ?? 'linear-gradient(155deg,#0E2C4C,#1E6FA8 55%,#3FA9E8)');

        if ($name === '' || $region === '' || $shortDesc === '') {
            flash('error', 'Name, region and short description are required.');
        } else {
            $newImage = save_destination_image($_FILES['image'] ?? [], $name);
            if ($id) {
                $cur = $db->prepare('SELECT image FROM destinations WHERE id = :id');
                $cur->execute(['id' => $id]);
                $image = $cur->fetchColumn() ?: null;
                if (!empty($_POST['remove_image'])) { $image = null; }
                if ($newImage) { $image = $newImage; }
                $stmt = $db->prepare('UPDATE destinations SET name=:name, region=:region, tag=:tag, short_desc=:sd, long_desc=:ld, best_time=:bt, duration=:du, highlight=:hl, icon=:icon, gradient=:grad, image=:img WHERE id=:id');
                $stmt->execute(['name'=>$name,'region'=>$region,'tag'=>$tag,'sd'=>$shortDesc,'ld'=>$longDesc,'bt'=>$bestTime,'du'=>$duration,'hl'=>$highlight,'icon'=>$icon,'grad'=>$gradient,'img'=>$image,'id'=>$id]);
                log_activity("Updated destination #$id ($name)");
                flash('success', 'Destination updated.');
            } else {
                $nextOrder = (int)$db->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM destinations')->fetchColumn();
                $stmt = $db->prepare('INSERT INTO destinations (name,region,tag,short_desc,long_desc,best_time,duration,highlight,icon,gradient,image,sort_order) VALUES (:name,:region,:tag,:sd,:ld,:bt,:du,:hl,:icon,:grad,:img,:so)');
                $stmt->execute(['name'=>$name,'region'=>$region,'tag'=>$tag,'sd'=>$shortDesc,'ld'=>$longDesc,'bt'=>$bestTime,'du'=>$duration,'hl'=>$highlight,'icon'=>$icon,'grad'=>$gradient,'img'=>$newImage,'so'=>$nextOrder]);
                log_activity("Added destination ($name)");
                flash('success', 'Destination added.');
            }
        }
        header('Location: destinations.php'); exit;
    }
}

if (!empty($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM destinations WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}

$destinations = $db->query('SELECT * FROM destinations ORDER BY sort_order ASC, name ASC')->fetchAll();
require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="page-head">
  <div>
    <h1>Destinations</h1>
    <p>Places shown in the Destinations section of the public site.</p>
  </div>
</div>

<div class="card">
  <h3><?= $editing ? 'Edit Destination' : 'Add New Destination' ?></h3>
  <form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : '' ?>">
    <div class="form-grid">
      <div class="form-group"><label>Name</label><input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Region</label>
        <select name="region" required>
          <?php foreach (['Cultural Triangle','Hill Country','South Coast','Wildlife'] as $r): ?>
            <option value="<?= e($r) ?>" <?= (($editing['region'] ?? '') === $r) ? 'selected' : '' ?>><?= e($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Tag line</label><input type="text" name="tag" value="<?= e($editing['tag'] ?? '') ?>"></div>
      <div class="form-group"><label>Icon (Font Awesome class)</label><input type="text" name="icon" placeholder="fa-mountain" value="<?= e($editing['icon'] ?? 'fa-map-pin') ?>"></div>
      <div class="form-group full"><label>Short description (card)</label><input type="text" name="short_desc" required value="<?= e($editing['short_desc'] ?? '') ?>"></div>
      <div class="form-group full"><label>Long description (popup)</label><textarea name="long_desc"><?= e($editing['long_desc'] ?? '') ?></textarea></div>
      <div class="form-group"><label>Best time to visit</label><input type="text" name="best_time" value="<?= e($editing['best_time'] ?? '') ?>"></div>
      <div class="form-group"><label>Suggested duration</label><input type="text" name="duration" value="<?= e($editing['duration'] ?? '') ?>"></div>
      <div class="form-group full"><label>Highlight</label><input type="text" name="highlight" value="<?= e($editing['highlight'] ?? '') ?>"></div>
      <div class="form-group full"><label>Photo (JPG / PNG / WEBP, max 5 MB)</label>
        <?php if (!empty($editing['image'])): ?>
          <img src="../<?= e($editing['image']) ?>" alt="" style="max-width:220px; border-radius:10px; margin-bottom:8px;">
          <label style="font-weight:400;"><input type="checkbox" name="remove_image" value="1"> Remove current photo</label>
        <?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </div>
      <div class="form-group full"><label>Card gradient (CSS) — fallback when there is no photo</label><input type="text" name="gradient" value="<?= e($editing['gradient'] ?? 'linear-gradient(155deg,#0E2C4C,#1E6FA8 55%,#3FA9E8)') ?>"></div>
    </div>
    <div class="action-row">
      <button type="submit" class="btn btn-blue"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Update Destination' : 'Add Destination' ?></button>
      <?php if ($editing): ?><a href="destinations.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3>All Destinations (<?= count($destinations) ?>)</h3>
  <table>
    <thead><tr><th>Photo</th><th>Name</th><th>Region</th><th>Tag</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($destinations as $d): ?>
      <tr>
        <td><?php if (!empty($d['image'])): ?><img src="../<?= e($d['image']) ?>" alt="" style="width:54px;height:40px;object-fit:cover;border-radius:6px;"><?php else: ?>—<?php endif; ?></td>
        <td><i class="fa-solid <?= e($d['icon']) ?>" style="color:var(--blue-soft); margin-right:8px;"></i><?= e($d['name']) ?></td>
        <td><?= e($d['region']) ?></td>
        <td><?= e($d['tag']) ?></td>
        <td class="action-row">
          <a href="?edit=<?= (int)$d['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
          <form method="POST" onsubmit="return confirm('Delete this destination?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
