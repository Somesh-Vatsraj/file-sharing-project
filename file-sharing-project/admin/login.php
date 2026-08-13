<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!login_allowed()) $error = 'Too many login attempts. Try again later.';
    else {
        verify_csrf();
        $identity = trim((string)($_POST['identity'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $s = db()->prepare("SELECT * FROM admins WHERE (username=? OR email=?) AND status='active' LIMIT 1");
        $s->execute([$identity, $identity]);
        $a = $s->fetch();
        if ($a && password_verify($password, $a['password'])) {
            login_admin($a);
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login — ShareVault</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="login-body">
    <div class="login-card"><a href="../index.php" class="admin-brand">ShareVault</a>
        <h1>Admin Login</h1>
        <p>Secure administration panel</p><?php if ($error): ?><div class="login-error"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><label>Username or email<input name="identity" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="admin-btn" type="submit">Sign in</button></form>
    </div>
</body>

</html>