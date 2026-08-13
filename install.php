<?php

declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$ok = version_compare(PHP_VERSION, '8.2.0', '>=') && extension_loaded('pdo_mysql') && extension_loaded('fileinfo') && extension_loaded('mbstring');
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ok) {
    $host = $_POST['host'] ?? DB_HOST;
    $name = $_POST['name'] ?? DB_NAME;
    $user = $_POST['user'] ?? DB_USER;
    $pass = $_POST['pass'] ?? DB_PASS;
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $sql = file_get_contents(__DIR__ . '/database.sql');
        $pdo->exec($sql);
        $msg = 'Database imported. Edit config/config.php if needed, then remove install.php.';
    } catch (Throwable $e) {
        $msg = 'Installation failed. Check database credentials and permissions.';
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ShareVault Installer</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="center-page">
    <div class="message-card">
        <h1>ShareVault Installer</h1>
        <p>PHP <?= e(PHP_VERSION) ?> · Requirements <?= $ok ? 'OK' : 'Not met' ?></p><?php if ($msg): ?><div class="alert <?= str_contains($msg, 'failed') ? 'error' : '' ?>"><?= e($msg) ?></div><?php endif; ?><form method="post" style="display:grid;gap:10px;text-align:left"><input name="host" placeholder="DB host" value="<?= e(DB_HOST) ?>"><input name="name" placeholder="DB name" value="<?= e(DB_NAME) ?>"><input name="user" placeholder="DB user" value="<?= e(DB_USER) ?>"><input type="password" name="pass" placeholder="DB password"><button class="btn primary" <?= $ok ? '' : 'disabled' ?>>Install Database</button></form>
        <p style="font-size:.8rem">After installation, delete install.php and create your first admin using the README instructions.</p>
    </div>
</body>

</html>