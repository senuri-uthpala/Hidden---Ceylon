<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: administrators.php');
    exit;
}

// Registration only works while an invite code is configured (config/config.php).
$registrationOpen = defined('ADMIN_INVITE_CODE') && ADMIN_INVITE_CODE !== '';
$errors = [];
$old    = ['full_name' => '', 'username' => ''];

if ($registrationOpen && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $fullName = clean($_POST['full_name'] ?? '');
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $code     = trim($_POST['invite_code'] ?? '');
    $old      = ['full_name' => $fullName, 'username' => $username];

    if (!hash_equals(ADMIN_INVITE_CODE, $code)) {
        $errors[] = 'That invite code is not valid.';
    } else {
        if ($fullName === '' || mb_strlen($fullName) > 100) { $errors[] = 'Please enter your full name (max 100 characters).'; }
        if ($msg = validate_username($username))            { $errors[] = $msg; }
        if ($msg = validate_password($password))            { $errors[] = $msg; }
        if ($password !== $confirm)                         { $errors[] = 'The two passwords do not match.'; }
        if (!$errors && admin_username_taken($username))    { $errors[] = 'That username is already taken.'; }

        if (!$errors) {
            try {
                $stmt = getDB()->prepare('INSERT INTO admin_users (username, password_hash, full_name) VALUES (:u, :p, :n)');
                $stmt->execute(['u' => $username, 'p' => password_hash($password, PASSWORD_DEFAULT), 'n' => $fullName]);
                log_activity("Registered admin account ($username)");
                flash('success', 'Account created — you can log in now.');
                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Could not create the account. That username may already exist.';
            }
        }
    }
}

$authTitle = 'Create Admin Account';
require __DIR__ . '/../includes/auth_header.php';
?>
    <h1>Create Admin Account</h1>
    <p class="auth-sub">Register to manage Hidden Ceylon</p>

<?php if (!$registrationOpen): ?>
    <div class="auth-msg error">Registration is switched off. Ask the site owner to set <code>ADMIN_INVITE_CODE</code> in <code>config/config.php</code>.</div>
<?php else: ?>
    <?php if ($errors): ?>
      <div class="auth-msg error"><ul><?php foreach ($errors as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST" novalidate autocomplete="off">
      <?= csrf_field() ?>
      <div class="field">
        <i class="fa-regular fa-id-card ico"></i>
        <input type="text" name="full_name" placeholder="Full name" aria-label="Full name" maxlength="100" required autofocus value="<?= e($old['full_name']) ?>">
      </div>
      <div class="field">
        <i class="fa-regular fa-user ico"></i>
        <input type="text" name="username" placeholder="Username" aria-label="Username" maxlength="30" autocomplete="off" required value="<?= e($old['username']) ?>">
      </div>
      <div class="field">
        <i class="fa-solid fa-lock ico"></i>
        <input type="password" name="password" placeholder="Password (8+ characters, letters &amp; numbers)" aria-label="Password" autocomplete="new-password" required>
        <button type="button" class="eye" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
      </div>
      <div class="field">
        <i class="fa-solid fa-lock ico"></i>
        <input type="password" name="confirm" placeholder="Confirm password" aria-label="Confirm password" autocomplete="new-password" required>
      </div>
      <div class="field">
        <i class="fa-solid fa-key ico"></i>
        <input type="text" name="invite_code" placeholder="Invite code" aria-label="Invite code" autocomplete="off" spellcheck="false" required>
      </div>
      <button type="submit" class="auth-btn"><i class="fa-solid fa-user-plus"></i> Create Account</button>
    </form>
    <p class="auth-note">The invite code is set in <code>config/config.php</code>.</p>
<?php endif; ?>

    <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
    <a href="../index.php" class="auth-back"><i class="fa-solid fa-arrow-left"></i> Back to website</a>
<?php require __DIR__ . '/../includes/auth_footer.php'; ?>
