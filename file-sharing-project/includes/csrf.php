<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): void
{
    start_secure_session();
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    if (!$token || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
        http_response_code(403);
        exit('Invalid security token.');
    }
}
