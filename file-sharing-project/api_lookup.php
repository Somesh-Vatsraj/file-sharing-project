<?php

declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
if (!rate_limit('lookup', RATE_MAX_LOOKUPS)) json_response(['success' => false, 'message' => 'Too many code attempts. Please try again later.'], 429);

$code = strtoupper(trim((string)($_POST['code'] ?? '')));
if (!preg_match('/^[A-Z0-9]{6,12}$/', $code)) json_response(['success' => false, 'message' => 'Enter a valid sharing code.'], 422);

$stmt = db()->prepare("SELECT * FROM files WHERE sharing_code=? LIMIT 1");
$stmt->execute([$code]);
$file = $stmt->fetch();
if (!$file) json_response(['success' => false, 'message' => 'Sharing code not found.'], 404);

$status = status_for_file($file);
if ($status !== 'active') {
    if ($status !== $file['status']) db()->prepare("UPDATE files SET status=? WHERE id=?")->execute([$status, (int)$file['id']]);
    $messages = ['expired' => 'This file has expired.', 'download_limit_reached' => 'This file has reached its maximum download limit.', 'disabled' => 'This file is disabled.', 'deleted' => 'This file is no longer available.'];
    json_response(['success' => false, 'message' => $messages[$status] ?? 'This file is unavailable.'], 410);
}
if (!is_file(STORAGE_PATH . DIRECTORY_SEPARATOR . basename($file['stored_name']))) json_response(['success' => false, 'message' => 'This file is no longer available.'], 404);

json_response(['success' => true, 'file' => [
    'name' => $file['original_name'],
    'size' => (int)$file['file_size'],
    'expires_at' => $file['expires_at'],
    'download_count' => (int)$file['download_count'],
    'max_downloads' => (int)$file['max_downloads'],
    'code' => $file['sharing_code']
]]);
