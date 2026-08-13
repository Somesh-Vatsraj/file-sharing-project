<?php

declare(strict_types=1);

const APP_NAME = 'ShareVault';
const APP_VERSION = '1.0.0';


define(
    'STORAGE_PATH',
    dirname(__DIR__) . DIRECTORY_SEPARATOR .
        'storage' . DIRECTORY_SEPARATOR .
        'files'
);
// define('BASE_PATH', dirname(__DIR__));
//define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'files');

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Kolkata');

const DB_HOST = '127.0.0.1';
const DB_NAME = 'file_sharing';
const DB_USER = 'root';
const DB_PASS = '';

const SESSION_NAME = 'sharevault_session';
const SESSION_TIMEOUT = 1800;
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 900;

const RATE_WINDOW_SECONDS = 300;
const RATE_MAX_LOOKUPS = 30;
const RATE_MAX_UPLOADS = 10;
const RATE_MAX_DOWNLOADS = 30;

const ALLOWED_DEFAULT_EXTENSIONS = 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,webp,zip,rar,7z,mp3,wav,mp4,mov,mkv';

if (!is_dir(STORAGE_PATH)) {
    @mkdir(STORAGE_PATH, 0750, true);
}

if (!defined('RATE_MAX_DOWNLOADS')) {
    define('RATE_MAX_DOWNLOADS', 30);
}

if (!defined('RATE_MAX_UPLOADS')) {
    define('RATE_MAX_UPLOADS', 10);
}

if (!defined('RATE_MAX_LOOKUPS')) {
    define('RATE_MAX_LOOKUPS', 30);
}
