<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$db = getDB();
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM packages WHERE id = :id')->execute(['id' => $id]);
        log_activity("Deleted package #$id");
        flash('success', 'Package deleted.');
        header('Location: packages.php'); exit;
    }

    if ($action === 'save') {
        $id        = (int)($_POST['id'] ?? 0);
        $name      = clean($_POST['name'] ?? '');
        $days      = clean($_POST['days_label'] ?? '');
        $price     = (float)($_POST['price'] ?? 0);
        $desc      = clean($_POST['description'] ?? '');
        $itemsRaw  = $_POST['items'] ?? '';
        $items     = array_values(array_filter(array_map('trim', explode("\n", $itemsRaw))));
        $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

        if ($name === '' || $days === '' || $price <= 0) {
            flash('error', 'Name, duration and a valid price are required.');
        } else {
            if ($id) {
                $stmt = $db->prepare('UPDATE packages SET name=:name, days_label=:days, price=:price, description=:desc, items_json=:items WHERE id=:id');
                $stmt->execute(['name'=>$name,'days'=>$days,'price'=>$price,'desc'=>$desc,'items'=>$itemsJson,'id'=>$id]);
                log_activity("Updated package #$id ($name)");
                flash('success', 'Package updated.');
            } else {
                $nextOrder = (int)$db->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM packages')->fetchColumn();
                $stmt = $db->prepare('INSERT INTO packages (name, days_label, price, description, items_json, sort_order) VALUES (:name,:days,:price,:desc,:items,:so)');
                $stmt->execute(['name'=>$name,'days'=>$days,'price'=>$price,'desc'=>$desc,'items'=>$itemsJson,'so'=>$nextOrder]);
                log_activity("Added package ($name)");
                flash('success', 'Package added.');
            }
        }
        header('Location: packages.php'); exit;
    }
}

if (!empty($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM packages WHERE id = :id');
    $stmt->execute(['id' => (int)$_GET['edit']]);
    $editing = $stmt->fetch();
}

$packages = $db->query('SELECT * FROM packages ORDER BY sort_order ASC, id ASC')->fetchAll();
require_once __DIR__ . '/../includes/admin_header.php';

$editingItemsText = '';
if ($editing) {
    $arr = json_decode($editing['items_json'], true) ?: [];
    $editingItemsText = implode("\n", $arr);
}
?>
<div class="page-head">
  <div>
    <h1>Tour Packages</h1>
    <p>Pre-built itineraries shown in the Packages section of the public site.</p>
  </div>
</div>

<div class="card">
  <h3><?= $editing ? 'Edit Package' : 'Add New Package' ?></h3>
  <form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : '' ?>">
    <div class="form-grid">
      <div class="form-group"><label>Package name</label><input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Duration label</label><input type="text" name="days_label" placeholder="e.g. 4 Days" required value="<?= e($editing['days_label'] ?? '') ?>"></div>
      <div class="form-group"><label>Price per person (USD)</label><input type="number" step="0.01" min="0" name="price" required value="<?= e((string)($editing['price'] ?? '')) ?>"></div>
      <div class="form-group full"><label>Short description</label><input type="text" name="description" value="<?= e($editing['description'] ?? '') ?>"></div>
      <div class="form-group full">
        <label>Included items (one per line)</label>
        <textarea name="items" rows="5" placeholder="Private AC vehicle throughout&#10;Sigiriya Rock sunrise climb"><?= e($editingItemsText) ?></textarea>
        <div class="hint">Each line becomes one bullet point on the package card.</div>
      </div>
    </div>
    <div class="action-row">
      <button type="submit" class="btn btn-blue"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Update Package' : 'Add Package' ?></button>
      <?php if ($editing): ?><a href="packages.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3>All Packages (<?= count($packages) ?>)</h3>
  <table>
    <thead><tr><th>Name</th><th>Duration</th><th>Price</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($packages as $p): ?>
      <tr>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['days_label']) ?></td>
        <td>$<?= number_format((float)$p['price'], 2) ?></td>
        <td class="action-row">
          <a href="?edit=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
          <form method="POST" onsubmit="return confirm('Delete this package?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
