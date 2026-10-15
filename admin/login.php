<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = getDB()->prepare('SELECT * FROM admin_users WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];
            log_activity('Logged in');
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

// Show the seeded demo login only while its password has not been changed.
$showDefaultHint = false;
try {
    $row = getDB()->query("SELECT password_hash FROM admin_users WHERE username = 'admin' LIMIT 1")->fetch();
    $showDefaultHint = $row && password_verify('Admin@123', $row['password_hash']);
} catch (Throwable $e) {
    // never block the login page over a hint
}

$flash     = get_flash();
$authTitle = 'Admin Login';
require __DIR__ . '/../includes/auth_header.php';
?>
    <h1>Admin Login</h1>
    <p class="auth-sub">Sign in to access your dashboard</p>

    <?php if ($flash): ?><div class="auth-msg <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="auth-msg error"><?= e($error) ?></div><?php endif; ?>

    <form method="POST" novalidate autocomplete="on">
      <?= csrf_field() ?>
      <div class="field">
        <i class="fa-regular fa-user ico"></i>
        <input type="text" name="username" placeholder="Username" aria-label="Username" autocomplete="username" required autofocus value="<?= e($username) ?>">
      </div>
      <div class="field">
        <i class="fa-solid fa-lock ico"></i>
        <input type="password" name="password" placeholder="Password" aria-label="Password" autocomplete="current-password" required>
        <button type="button" class="eye" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
      </div>
      <button type="submit" class="auth-btn"><i class="fa-solid fa-right-to-bracket"></i> Log In</button>
    </form>

    <?php if ($showDefaultHint): ?>
      <div class="auth-divider"><span>Default login: <strong>admin</strong> / <strong>Admin@123</strong></span></div>
    <?php endif; ?>

    <p class="auth-switch">Need an admin account? <a href="register.php">Register</a></p>
    <a href="../index.php" class="auth-back"><i class="fa-solid fa-arrow-left"></i> Back to website</a>
<?php require __DIR__ . '/../includes/auth_footer.php'; ?>
