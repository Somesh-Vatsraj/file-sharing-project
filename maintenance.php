<?php

declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
$c = app_config();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Maintenance — <?= e($c['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="center-page" style="--primary:<?= e($c['primary']) ?>;--secondary:<?= e($c['secondary']) ?>;--bg:<?= e($c['background']) ?>">
    <div class="message-card">
        <div class="drop-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <h1>We'll be back shortly.</h1>
        <p>This website is temporarily unavailable for maintenance.</p>
    </div>
</body>

</html>