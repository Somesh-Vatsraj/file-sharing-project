<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/database.php';

function normalized_extensions(): array
{
    $raw = (string)setting('allowed_extensions', ALLOWED_DEFAULT_EXTENSIONS);
    $parts = preg_split('/[,\s]+/', strtolower($raw), -1, PREG_SPLIT_NO_EMPTY);
    $out = [];
    foreach ($parts as $ext) {
        $ext = ltrim($ext, '.');
        if (preg_match('/^[a-z0-9]{1,10}$/', $ext)) $out[$ext] = true;
    }
    return array_keys($out);
}

function generate_share_code(): string
{
    $length = min(12, max(6, int_setting('code_length', 6)));
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($alphabet) - 1;
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }
        $stmt = db()->prepare("SELECT id FROM files WHERE sharing_code = ? LIMIT 1");
        $stmt->execute([$code]);
    } while ($stmt->fetchColumn());

    return $code;
}

function safe_original_name(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?? 'file';
    $name = trim($name);
    if ($name === '') $name = 'file';
    return mb_substr($name, 0, 255);
}

function validate_upload(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $map = [
            UPLOAD_ERR_INI_SIZE => 'The file exceeds the server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'The file exceeds the allowed form size.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted.',
            UPLOAD_ERR_NO_FILE => 'Please select a file.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server could not write the upload.',
        ];
        throw new RuntimeException($map[$file['error']] ?? 'Upload failed.');
    }

    $size = (int)$file['size'];
    $maxMb = max(1, int_setting('max_file_size_mb', 100));
    $maxBytes = $maxMb * 1024 * 1024;
    if ($size <= 0 || $size > $maxBytes) {
        throw new RuntimeException("Maximum allowed file size is {$maxMb} MB.");
    }

    $name = safe_original_name((string)$file['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = normalized_extensions();

    if (!$ext || !in_array($ext, $allowed, true)) {
        throw new RuntimeException('This file type is not allowed.');
    }

    $blocked = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'py', 'exe', 'sh', 'bat', 'cmd', 'com', 'scr', 'msi', 'dll'];
    if (in_array($ext, $blocked, true)) {
        throw new RuntimeException('Executable or server-side script files are blocked.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string)$file['tmp_name']);
    if (!$mime) throw new RuntimeException('Could not determine file type.');

    return ['name' => $name, 'ext' => $ext, 'mime' => $mime, 'size' => $size];
}

function store_uploaded_file(array $file): array
{
    $meta = validate_upload($file);
    $stored = bin2hex(random_bytes(24)) . '.bin';
    $destination = STORAGE_PATH . DIRECTORY_SEPARATOR . $stored;

    if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not store the uploaded file.');
    }
    @chmod($destination, 0640);

    $expiresHours = max(1, int_setting('code_expiry_hours', 24));
    $maxDownloads = max(1, int_setting('max_downloads', 5));
    $code = generate_share_code();

    try {
        $stmt = db()->prepare("INSERT INTO files
            (original_name, stored_name, mime_type, file_size, sharing_code, status, download_count, max_downloads, expires_at)
            VALUES (?, ?, ?, ?, ?, 'active', 0, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))");
        $stmt->execute([$meta['name'], $stored, $meta['mime'], $meta['size'], $code, $maxDownloads, $expiresHours]);
    } catch (Throwable $e) {
        @unlink($destination);
        throw $e;
    }

    return db()->query("SELECT * FROM files WHERE id = " . (int)db()->lastInsertId())->fetch();
}
