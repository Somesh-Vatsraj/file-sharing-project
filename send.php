<?php

declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
if (maintenance_active()) {
    require __DIR__ . '/maintenance.php';
    exit;
}
if (!bool_setting('upload_enabled', true)) {
    http_response_code(503);
    require __DIR__ . '/403.php';
    exit;
}
$c = app_config();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Send File — <?= e($c['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" defer></script>
</head>

<body style="--primary:<?= e($c['primary']) ?>;--secondary:<?= e($c['secondary']) ?>;--bg:<?= e($c['background']) ?>;--radius:<?= e($c['radius']) ?>">
    <header class="nav"><a class="brand" href="index.php"><?= e($c['name']) ?></a>
        <nav><a href="index.php">Home</a><a class="active" href="send.php">Send File</a><a href="receive.php">Receive File</a></nav>
    </header>
    <main class="container page">
        <div class="page-title"><span class="eyebrow">SEND FILE</span>
            <h1>Upload a file securely.</h1>
            <p>Drag a file here, or browse from your device. The server validates the upload before storing it.</p>
        </div>
        <section class="upload-panel">
            <div id="dropZone" class="dropzone"><input id="fileInput" type="file" hidden>
                <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <h2>Drop your file here</h2>
                <p>or <button type="button" id="browseBtn" class="link-btn">choose a file</button></p><small>Maximum <?= e((string)setting('max_file_size_mb', '100')) ?> MB • Allowed: <?= e((string)setting('allowed_extensions', ALLOWED_DEFAULT_EXTENSIONS)) ?></small>
            </div>
            <div id="filePreview" class="file-preview hidden">
                <div class="file-icon"><i class="fa-solid fa-file"></i></div>
                <div class="file-meta"><strong id="fileName"></strong><span id="fileSize"></span></div><button id="removeFile" class="icon-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="progressWrap" class="progress-wrap hidden">
                <div class="progress-top"><span id="progressText">Uploading…</span><span id="progressPct">0%</span></div>
                <div class="progress">
                    <div id="progressBar"></div>
                </div>
                <div class="speed-row"><span id="speed"></span><span id="eta"></span></div>
            </div>
            <button id="uploadBtn" class="btn primary full" disabled><i class="fa-solid fa-arrow-up-from-bracket"></i> Upload File</button>
            <div id="result" class="result hidden"></div>
            <div id="uploadError" class="alert error hidden"></div>
        </section>
    </main>
    <script src="assets/js/upload.js"></script>
</body>

</html>