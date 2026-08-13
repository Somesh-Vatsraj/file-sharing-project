<?php

declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (maintenance_active()) {
    require __DIR__ . '/maintenance.php';
    exit;
}
$c = app_config();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Receive File — <?= e($c['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" defer></script>
</head>

<body style="--primary:<?= e($c['primary']) ?>;--secondary:<?= e($c['secondary']) ?>;--bg:<?= e($c['background']) ?>">
    <header class="nav"><a class="brand" href="index.php"><?= e($c['name']) ?></a>
        <nav><a href="index.php">Home</a><a href="send.php">Send File</a><a class="active" href="receive.php">Receive File</a></nav>
    </header>
    <main class="container page">
        <div class="page-title"><span class="eyebrow">RECEIVE FILE</span>
            <h1>Enter your sharing code.</h1>
            <p>Use the code provided by the sender to retrieve the file.</p>
        </div>
        <section class="receive-panel">
            <form id="lookupForm"><label for="code">Sharing code</label>
                <div class="code-input"><i class="fa-solid fa-key"></i><input id="code" name="code" maxlength="12" autocomplete="off" placeholder="A7K92P" required><button class="btn primary" type="submit">Verify</button></div>
            </form>
            <div id="lookupError" class="alert error hidden"></div>
            <div id="fileCard" class="download-card hidden">
                <div class="file-icon big"><i class="fa-solid fa-file-arrow-down"></i></div>
                <h2 id="downloadName"></h2>
                <div class="download-meta"><span id="downloadSize"></span><span id="downloadExpiry"></span><span id="downloadCount"></span></div><a id="downloadBtn" class="btn primary full" href="#"><i class="fa-solid fa-download"></i> Download File</a>
            </div>
        </section>
    </main>
    <script src="assets/js/receive.js"></script>
</body>

</html>