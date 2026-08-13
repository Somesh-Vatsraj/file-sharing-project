<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    logout_admin();
}
header('Location: login.php');
exit;
