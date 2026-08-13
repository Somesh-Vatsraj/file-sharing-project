<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../config/database.php';

function admin_logged_in(): bool
{
    start_secure_session();
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function login_admin(array $admin): void
{
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['_created'] = time();
    db()->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([(int)$admin['id']]);
}

function logout_admin(): void
{
    start_secure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}

function login_allowed(): bool
{
    return rate_limit('admin-login', MAX_LOGIN_ATTEMPTS, LOGIN_LOCK_SECONDS);
}
