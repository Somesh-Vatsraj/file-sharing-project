<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Files';
require __DIR__ . '/includes/header.php';

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $s = $pdo->prepare("SELECT * FROM files WHERE id=?");
    $s->execute([$id]);
    $f = $s->fetch();
    if ($f) {
        $path = STORAGE_PATH . '/' . basename($f['stored_name']);
        if ($action === 'delete') {
            if (is_file($path)) @unlink($path);
            $pdo->prepare("DELETE FROM files WHERE id=?")->execute([$id]);
        } elseif ($action === 'disable') $pdo->prepare("UPDATE files SET status='disabled' WHERE id=?")->execute([$id]);
        elseif ($action === 'enable') $pdo->prepare("UPDATE files SET status='active' WHERE id=? AND expires_at>NOW()")->execute([$id]);
        elseif ($action === 'expire') $pdo->prepare("UPDATE files SET status='expired',expires_at=NOW() WHERE id=?")->execute([$id]);
    }
    header('Location: files.php');
    exit;
}
$q = trim((string)($_GET['q'] ?? ''));
$filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 15;
$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(original_name LIKE ? OR sharing_code LIKE ? OR id=?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = (int)$q;
}
if (in_array($filter, ['active', 'expired', 'disabled', 'download_limit_reached'], true)) {
    $where[] = 'status=?';
    $params[] = $filter;
}
$countSql = 'SELECT COUNT(*) FROM files' . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $per));
$sql = 'SELECT * FROM files' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per);
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
?><section class="panel">
    <div class="panel-head">
        <h2>Files</h2>
        <form class="filters"><input name="q" value="<?= e($q) ?>" placeholder="Search file, code or ID"><select name="status">
                <option value="">All statuses</option><?php foreach (['active', 'expired', 'disabled', 'download_limit_reached'] as $s): ?><option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
            </select><button class="admin-btn small">Search</button></form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>File</th>
                    <th>Stored</th>
                    <th>Code</th>
                    <th>Size</th>
                    <th>Expiry</th>
                    <th>Downloads</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?><tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= e($r['original_name']) ?></td>
                        <td><code><?= e($r['stored_name']) ?></code></td>
                        <td><code><?= e($r['sharing_code']) ?></code></td>
                        <td><?= e(format_bytes((int)$r['file_size'])) ?></td>
                        <td><?= e($r['expires_at']) ?></td>
                        <td><?= $r['download_count'] ?> / <?= $r['max_downloads'] ?></td>
                        <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                        <td>
                            <div class="actions-mini"><?php if ($r['status'] === 'active'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="disable"><button title="Disable">Disable</button></form><?php else: ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="enable"><button>Enable</button></form><?php endif; ?><form method="post" onsubmit="return confirm('Delete this file permanently?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="expire"><button>Expire</button></form>
                                <form method="post" onsubmit="return confirm('Delete this file permanently?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="danger">Delete</button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<div class="pagination"><?php if ($page > 1): ?><a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">← Previous</a><?php endif; ?><span>Page <?= $page ?> of <?= $totalPages ?></span><?php if ($page < $totalPages): ?><a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next →</a><?php endif; ?></div>
<?php require __DIR__ . '/includes/footer.php'; ?>