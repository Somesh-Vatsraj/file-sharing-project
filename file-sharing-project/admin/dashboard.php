<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Dashboard';
require __DIR__ . '/includes/header.php';

$pdo = db();

cleanup_expired(false);
$total = (int)$pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
$active = (int)$pdo->query("SELECT COUNT(*) FROM files WHERE status='active'")->fetchColumn();
$expired = (int)$pdo->query("SELECT COUNT(*) FROM files WHERE status='expired'")->fetchColumn();
$disabled = (int)$pdo->query("SELECT COUNT(*) FROM files WHERE status='disabled'")->fetchColumn();
$downloads = (int)$pdo->query("SELECT COUNT(*) FROM downloads WHERE status='success'")->fetchColumn();
$used = (int)$pdo->query("SELECT COALESCE(SUM(file_size),0) FROM files WHERE status<>'deleted'")->fetchColumn();
$free = disk_free_space(__DIR__ . '/..') ?: 0;
$largest = $pdo->query("SELECT original_name,file_size,sharing_code FROM files WHERE status<>'deleted' ORDER BY file_size DESC LIMIT 5")->fetchAll();
$recent = $pdo->query("SELECT * FROM files ORDER BY created_at DESC LIMIT 8")->fetchAll();
?>
<div class="cards">
    <div class="stat"><span>Total Files</span><strong><?= $total ?></strong></div>
    <div class="stat"><span>Active Shares</span><strong><?= $active ?></strong></div>
    <div class="stat"><span>Expired</span><strong><?= $expired ?></strong></div>
    <div class="stat"><span>Disabled</span><strong><?= $disabled ?></strong></div>
    <div class="stat"><span>Downloads</span><strong><?= $downloads ?></strong></div>
    <div class="stat"><span>Storage Used</span><strong><?= e(format_bytes($used)) ?></strong></div>
    <div class="stat"><span>Storage Available</span><strong><?= e(format_bytes($free)) ?></strong></div>
</div>
<section class="panel">
    <div class="panel-head">
        <h2>Recent Uploads</h2><a href="files.php">Manage all</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Code</th>
                    <th>Uploaded</th>
                    <th>Expiry</th>
                    <th>Downloads</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody><?php foreach ($recent as $r): ?><tr>
                        <td><?= e($r['original_name']) ?><small><?= e(format_bytes((int)$r['file_size'])) ?></small></td>
                        <td><code><?= e($r['sharing_code']) ?></code></td>
                        <td><?= e($r['created_at']) ?></td>
                        <td><?= e($r['expires_at']) ?></td>
                        <td><?= e((string)$r['download_count']) ?> / <?= e((string)$r['max_downloads']) ?></td>
                        <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<?php $recentD = $pdo->query("SELECT d.*,f.original_name,f.sharing_code FROM downloads d JOIN files f ON f.id=d.file_id ORDER BY d.downloaded_at DESC LIMIT 8")->fetchAll(); ?><section class="panel">
    <div class="panel-head">
        <h2>Recent Downloads</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Code</th>
                    <th>Time</th>
                    <th>IP</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody><?php foreach ($recentD as $d): ?><tr>
                        <td><?= e($d['original_name']) ?></td>
                        <td><code><?= e($d['sharing_code']) ?></code></td>
                        <td><?= e($d['downloaded_at']) ?></td>
                        <td><?= e($d['ip_address'] ?? '') ?></td>
                        <td><?= e($d['status']) ?></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<section class="panel">
    <div class="panel-head">
        <h2>Largest Files</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Code</th>
                    <th>Size</th>
                </tr>
            </thead>
            <tbody><?php foreach ($largest as $lf): ?><tr>
                        <td><?= e($lf['original_name']) ?></td>
                        <td><code><?= e($lf['sharing_code']) ?></code></td>
                        <td><?= e(format_bytes((int)$lf['file_size'])) ?></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>