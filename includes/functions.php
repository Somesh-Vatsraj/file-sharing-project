<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';

function format_bytes(int|float $bytes): string
{
    if ($bytes < 1024) return $bytes . ' B';
    $units = ['KB', 'MB', 'GB', 'TB'];
    $i = -1;
    do {
        $bytes /= 1024;
        $i++;
    } while ($bytes >= 1024 && $i < count($units) - 1);
    return number_format($bytes, $bytes >= 100 ? 0 : 1) . ' ' . $units[$i];
}

function status_for_file(array $file): string
{
    if (($file['status'] ?? '') === 'deleted') return 'deleted';
    if (($file['status'] ?? '') === 'disabled') return 'disabled';
    if (!empty($file['expires_at']) && strtotime($file['expires_at']) <= time()) return 'expired';
    if ((int)($file['download_count'] ?? 0) >= (int)($file['max_downloads'] ?? 0)) return 'download_limit_reached';
    return 'active';
}

function refresh_file_status(int $id): string
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    if (!$file) return 'deleted';

    $status = status_for_file($file);
    if ($status !== $file['status'] && $file['status'] !== 'deleted') {
        $pdo->prepare("UPDATE files SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $id]);
    }
    return $status;
}

function cleanup_expired(bool $deleteRecords = false): array
{
    $pdo = db();
    $rows = $pdo->query("SELECT id, stored_name, status FROM files WHERE expires_at <= NOW() AND status NOT IN ('deleted','disabled')")->fetchAll();
    $deleted = 0;
    $marked = 0;
    foreach ($rows as $row) {
        $path = STORAGE_PATH . DIRECTORY_SEPARATOR . basename($row['stored_name']);
        if (is_file($path)) @unlink($path);
        if ($deleteRecords) {
            $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([(int)$row['id']]);
            $deleted++;
        } else {
            $pdo->prepare("UPDATE files SET status='expired', updated_at=NOW() WHERE id=?")->execute([(int)$row['id']]);
            $marked++;
        }
    }
    return ['deleted' => $deleted, 'marked' => $marked];
}

function app_config(): array
{
    return [
        'name' => (string)setting('website_name', APP_NAME),
        'description' => (string)setting('website_description', 'Secure file sharing from any device.'),
        'contact_email' => (string)setting('contact_email', ''),
        'contact_phone' => (string)setting('contact_phone', ''),
        'footer_text' => (string)setting('footer_text', 'Secure file sharing made simple.'),
        'primary' => (string)setting('primary_color', '#7c3aed'),
        'secondary' => (string)setting('secondary_color', '#2563eb'),
        'background' => (string)setting('background_color', '#070b16'),
        'radius' => (string)setting('border_radius', '18px'),
        'hero_heading' => (string)setting('hero_heading', 'Send Files. Anywhere. Securely.'),
        'hero_paragraph' => (string)setting('hero_paragraph', 'Upload a file, get a secure sharing code, and download it from anywhere on the Internet.'),
    ];
}
