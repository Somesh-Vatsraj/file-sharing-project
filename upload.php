<?php

declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/csrf.php';

header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
verify_csrf();
if (maintenance_active() || !bool_setting('upload_enabled', true)) json_response(['success' => false, 'message' => 'Uploads are currently disabled.'], 503);
if (!rate_limit('upload', RATE_MAX_UPLOADS)) json_response(['success' => false, 'message' => 'Too many uploads. Please try again later.'], 429);

try {
    if (empty($_FILES['file'])) throw new RuntimeException('Please select a file.');
    $file = store_uploaded_file($_FILES['file']);
    json_response([
        'success' => true,
        'file' => [
            'name' => $file['original_name'],
            'size' => (int)$file['file_size'],
            'mime' => $file['mime_type'],
            'code' => $file['sharing_code'],
            'expires_at' => $file['expires_at'],
            'max_downloads' => (int)$file['max_downloads'],
            'download_count' => (int)$file['download_count'],
        ]
    ]);
} catch (Throwable $e) {
    app_log($e->getMessage());
    json_response(['success' => false, 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Upload failed.'], 400);
}
