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
<html lang="en" data-theme="<?= e((string)setting('theme', 'dark')) ?>">

<head>
    <?php $how = json_decode((string)setting('how_steps', '[]'), true) ?: [];
    $features = json_decode((string)setting('features', '[]'), true) ?: [];
    $faqItems = json_decode((string)setting('faq_items', '[]'), true) ?: []; ?>
    <style>
        <?= (string)setting('custom_css', '') ?>
    </style>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($c['name']) ?> — Secure File Sharing</title>
    <meta name="description" content="<?= e($c['description']) ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="<?= e((string)setting('favicon', '')) ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" defer></script>
</head>

<body style="--primary:<?= e($c['primary']) ?>;--secondary:<?= e($c['secondary']) ?>;--bg:<?= e($c['background']) ?>;--radius:<?= e($c['radius']) ?>">
    <header class="nav"><a class="brand" href="index.php"><?php if (setting('website_logo', '')): ?><img src="<?= e((string)setting('website_logo', '')) ?>" alt="<?= e($c['name']) ?>" style="height:34px;max-width:150px;object-fit:contain"><?php else: ?><?= e($c['name']) ?><?php endif; ?></a><button class="menu" id="menuBtn"><i class="fa-solid fa-bars"></i></button>
        <nav id="mainNav"><a href="index.php">Home</a><a href="send.php">Send File</a><a href="receive.php">Receive File</a><a href="#how">How It Works</a><a href="#features">Features</a><a href="#faq">FAQ</a><?php if ($c['contact_email']): ?><a href="mailto:<?= e($c['contact_email']) ?>">Contact</a><?php endif; ?><a href="admin/login.php">Admin Login</a><button id="themeBtn" class="icon-btn" title="Toggle theme"><i class="fa-solid fa-moon"></i></button></nav>
    </header>
    <main>
        <section class="hero container">
            <div class="hero-copy"><span class="eyebrow"><i class="fa-solid fa-shield-halved"></i> Secure, code-based sharing</span>
                <h1><?= e($c['hero_heading']) ?></h1>
                <p><?= e($c['hero_paragraph']) ?></p>
                <div class="actions"><a class="btn primary" href="send.php"><i class="fa-solid fa-arrow-up-from-bracket"></i> <?= e((string)setting('hero_send_text', 'Send File')) ?></a><a class="btn secondary" href="receive.php"><i class="fa-solid fa-download"></i> <?= e((string)setting('hero_receive_text', 'Receive File')) ?></a></div>
                <div class="trust"><i class="fa-solid fa-lock"></i> Server-side validation &nbsp;•&nbsp; Expiring codes &nbsp;•&nbsp; Download limits</div>
            </div>
            <div class="hero-art">
                <div class="orb"></div>
                <div class="transfer-card">
                    <div class="icon-bubble"><i class="fa-solid fa-file-arrow-up"></i></div>
                    <div><strong>Secure transfer</strong><small>Upload → Share code → Download</small></div><span class="pulse">●</span>
                </div>
                <div class="code-card"><small>SHARING CODE</small><strong>A7K92P</strong><span>Works anywhere online</span></div>
            </div>
        </section>
        <section id="how" class="section container">
            <div class="section-head"><span class="eyebrow">HOW IT WORKS</span>
                <h2>Three steps. Zero hassle.</h2>
            </div>
            <div class="steps">
                <article><span>01</span><i class="fa-solid fa-cloud-arrow-up"></i>
                    <h3>Upload File</h3>
                    <p>Select a file and upload it through the protected server endpoint.</p>
                </article>
                <article><span>02</span><i class="fa-solid fa-key"></i>
                    <h3>Share Code</h3>
                    <p>Get a cryptographically generated code with expiry and download limits.</p>
                </article>
                <article><span>03</span><i class="fa-solid fa-download"></i>
                    <h3>Download Anywhere</h3>
                    <p>Enter the code on another device and securely download the file.</p>
                </article>
            </div>
        </section>
        <section id="features" class="section alt">
            <div class="container">
                <div class="section-head"><span class="eyebrow">FEATURES</span>
                    <h2>Built for practical sharing.</h2>
                </div>
                <div class="feature-grid"><?php foreach ($features as $f): ?><article class="feature"><i class="fa-solid <?= e((string)($f['icon'] ?? 'fa-check')) ?>"></i>
                            <h3><?= e((string)($f['title'] ?? '')) ?></h3>
                            <p><?= e((string)($f['text'] ?? '')) ?></p>
                        </article><?php endforeach; ?></div>
            </div>
        </section>
        <section class="security section container">
            <div><span class="eyebrow">SECURITY</span>
                <h2>Designed to keep the file path private.</h2>
                <p>Files are stored with random physical names, outside the normal public URL flow where hosting permits. Download requests are checked against the database, expiry and limits before a stream starts.</p>
            </div>
            <div class="security-list">
                <div><i class="fa-solid fa-check"></i> PDO prepared statements</div>
                <div><i class="fa-solid fa-check"></i> Server-side MIME and extension validation</div>
                <div><i class="fa-solid fa-check"></i> CSRF protection for admin actions</div>
                <div><i class="fa-solid fa-check"></i> Rate limiting for sensitive endpoints</div>
            </div>
        </section>
        <section id="faq" class="section alt">
            <div class="container narrow">
                <div class="section-head"><span class="eyebrow">FAQ</span>
                    <h2>Common questions.</h2>
                </div><?php $faqs = [['Do users need an account?', 'No. Normal sending and receiving are account-free unless the administrator enables an account requirement in the settings.'], ['Does it require the same Wi-Fi?', 'No. It is a normal web application, so both devices can be anywhere with Internet access.'], ['Are files public?', 'No. The application uses a protected download endpoint and random storage names.'], ['How long does a share work?', 'The administrator controls the default expiry duration. Each upload receives its own expiry timestamp.']];
                        foreach ($faqs as $i => $faq): ?><details>
                        <summary><?= e($faq[0]) ?><i class="fa-solid fa-plus"></i></summary>
                        <p><?= e($faq[1]) ?></p>
                    </details><?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="container footer-inner">
            <div><strong><?= e($c['name']) ?></strong>
                <p><?= e($c['footer_text']) ?></p>
            </div>
            <div><?= $c['contact_email'] ? '<a href="mailto:' . e($c['contact_email']) . '">' . e($c['contact_email']) . '</a>' : '' ?></div>
        </div>
        <div class="copyright">© <?= date('Y') ?> <?= e($c['name']) ?>. All rights reserved.</div>
    </footer>
    <script src="assets/js/app.js"></script>
</body>

</html>