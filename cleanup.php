<?php

declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (PHP_SAPI !== 'cli' && !hash_equals((string)($_GET['key'] ?? ''), (string)setting('cleanup_key', ''))) {
    http_response_code(403);
    exit('Forbidden');
}
$result = cleanup_expired(bool_setting('auto_delete_expired', false));
echo "Marked: {$result['marked']}; Deleted: {$result['deleted']}\n";
