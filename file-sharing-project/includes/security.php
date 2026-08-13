<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    }
    if (time() - (int)$_SESSION['_created'] > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function app_log(string $message): void
{
    error_log('[ShareVault] ' . $message);
}

function setting(string $key, mixed $default = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $pdo = db();
            $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
            foreach ($rows as $row) $cache[$row['setting_key']] = $row['setting_value'];
        } catch (Throwable $e) {
            return $default;
        }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function bool_setting(string $key, bool $default = false): bool
{
    return filter_var(setting($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
}

function int_setting(string $key, int $default): int
{
    return max(0, (int)setting($key, (string)$default));
}

function app_installed(): bool
{
    try {
        db()->query("SELECT 1 FROM settings LIMIT 1");
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function maintenance_active(): bool
{
    return bool_setting('maintenance_mode', false);
}

function public_available(): bool
{
    return !maintenance_active();
}

function enforce_public_access(): void
{
    if (maintenance_active()) {
        require BASE_PATH . '/maintenance.php';
        exit;
    }
}

function rate_limit(string $bucket, int $max, int $window = RATE_WINDOW_SECONDS): bool
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sharevault-rate';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . DIRECTORY_SEPARATOR . hash('sha256', $bucket . '|' . client_ip()) . '.json';
    $now = time();
    $fp = @fopen($file, 'c+');
    if (!$fp) return true; // Fail open only if the host blocks temp-file access.

    flock($fp, LOCK_EX);
    $raw = stream_get_contents($fp);
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data) || ($now - (int)($data['start'] ?? 0)) >= $window) {
        $data = ['start' => $now, 'count' => 0];
    }
    $data['count'] = (int)$data['count'] + 1;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $data['count'] <= $max;
}
