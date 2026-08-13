<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';


/*
|--------------------------------------------------------------------------
| Download Setting
|--------------------------------------------------------------------------
*/

if (!bool_setting('download_enabled', true)) {

    http_response_code(503);

    exit('Downloads are currently disabled.');
}


/*
|--------------------------------------------------------------------------
| Rate Limit
|--------------------------------------------------------------------------
*/

if (
    defined('RATE_MAX_DOWNLOADS') &&
    !rate_limit(
        'download',
        RATE_MAX_DOWNLOADS
    )
) {

    http_response_code(429);

    exit('Too many download attempts. Please try again later.');
}


/*
|--------------------------------------------------------------------------
| Get Sharing Code
|--------------------------------------------------------------------------
*/

$code = strtoupper(
    trim(
        (string)(
            $_GET['code'] ?? ''
        )
    )
);


/*
|--------------------------------------------------------------------------
| Validate Sharing Code
|--------------------------------------------------------------------------
*/

if (
    !preg_match(
        '/^[A-Z0-9]{6,12}$/',
        $code
    )
) {

    http_response_code(404);

    exit('Invalid sharing code.');
}


/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

$pdo = null;

try {

    $pdo = db();


    /*
    |--------------------------------------------------------------------------
    | Start Transaction
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Find File
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            original_name,
            stored_name,
            mime_type,
            file_size,
            sharing_code,
            status,
            download_count,
            max_downloads,
            expires_at,
            created_at
        FROM files
        WHERE sharing_code = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        $code
    ]);

    $file = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | File Not Found
    |--------------------------------------------------------------------------
    */

    if (!$file) {

        throw new RuntimeException(
            'not_found'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Check Current Status
    |--------------------------------------------------------------------------
    */

    $status = status_for_file($file);


    if ($status !== 'active') {

        /*
         * Update expired/download-limit status
         */

        if (
            $status !==
            (string)$file['status']
        ) {

            $update = $pdo->prepare("
                UPDATE files
                SET
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $update->execute([
                $status,
                (int)$file['id']
            ]);
        }


        throw new RuntimeException(
            $status
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Storage Filename
    |--------------------------------------------------------------------------
    */

    $storedName = basename(
        (string)$file['stored_name']
    );


    /*
    |--------------------------------------------------------------------------
    | Physical File Path
    |--------------------------------------------------------------------------
    */

    $path =
        STORAGE_PATH .
        DIRECTORY_SEPARATOR .
        $storedName;


    /*
    |--------------------------------------------------------------------------
    | File Exists
    |--------------------------------------------------------------------------
    */

    if (!is_file($path)) {

        throw new RuntimeException(
            'missing'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | File Readable
    |--------------------------------------------------------------------------
    */

    if (!is_readable($path)) {

        throw new RuntimeException(
            'missing'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify File Size
    |--------------------------------------------------------------------------
    */

    $realFileSize = filesize($path);

    if ($realFileSize === false) {

        throw new RuntimeException(
            'missing'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Download Count
    |--------------------------------------------------------------------------
    */

    $currentDownloads =
        (int)$file['download_count'];

    $maximumDownloads =
        (int)$file['max_downloads'];


    /*
    |--------------------------------------------------------------------------
    | Extra Limit Check
    |--------------------------------------------------------------------------
    */

    if (
        $currentDownloads >=
        $maximumDownloads
    ) {

        $update = $pdo->prepare("
            UPDATE files
            SET
                status = 'download_limit_reached',
                updated_at = NOW()
            WHERE id = ?
        ");

        $update->execute([
            (int)$file['id']
        ]);

        throw new RuntimeException(
            'download_limit_reached'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Increment Download
    |--------------------------------------------------------------------------
    */

    $newDownloadCount =
        $currentDownloads + 1;


    if (
        $newDownloadCount >=
        $maximumDownloads
    ) {

        $newStatus =
            'download_limit_reached';
    } else {

        $newStatus =
            'active';
    }


    /*
    |--------------------------------------------------------------------------
    | Update File
    |--------------------------------------------------------------------------
    */

    $update = $pdo->prepare("
        UPDATE files
        SET
            download_count = ?,
            status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $update->execute([
        $newDownloadCount,
        $newStatus,
        (int)$file['id']
    ]);


    /*
    |--------------------------------------------------------------------------
    | Download Log
    |--------------------------------------------------------------------------
    */

    $log = $pdo->prepare("
        INSERT INTO downloads
        (
            file_id,
            ip_address,
            user_agent,
            status
        )
        VALUES
        (?, ?, ?, 'success')
    ");


    $ipAddress =
        function_exists('client_ip')
        ? client_ip()
        : (
            $_SERVER['REMOTE_ADDR']
            ?? null
        );


    $userAgent = mb_substr(
        (string)(
            $_SERVER['HTTP_USER_AGENT']
            ?? ''
        ),
        0,
        500
    );


    $log->execute([
        (int)$file['id'],
        $ipAddress,
        $userAgent
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Original Filename
    |--------------------------------------------------------------------------
    */

    if (
        function_exists('safe_original_name')
    ) {

        $downloadName =
            safe_original_name(
                (string)$file['original_name']
            );
    } else {

        $downloadName =
            basename(
                (string)$file['original_name']
            );

        $downloadName =
            str_replace(
                [
                    '"',
                    "\r",
                    "\n"
                ],
                '',
                $downloadName
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MIME Type
    |--------------------------------------------------------------------------
    */

    $mimeType =
        trim(
            (string)$file['mime_type']
        );


    if ($mimeType === '') {

        $mimeType =
            'application/octet-stream';
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Output Buffer
    |--------------------------------------------------------------------------
    */

    while (
        ob_get_level() > 0
    ) {

        ob_end_clean();
    }


    /*
    |--------------------------------------------------------------------------
    | Download Headers
    |--------------------------------------------------------------------------
    */

    header(
        'Content-Type: ' .
            $mimeType
    );


    header(
        'Content-Disposition: attachment; filename="' .
            addcslashes(
                $downloadName,
                "\"\\"
            ) .
            '"'
    );


    header(
        'Content-Length: ' .
            (string)$realFileSize
    );


    header(
        'Content-Transfer-Encoding: binary'
    );


    header(
        'Accept-Ranges: bytes'
    );


    header(
        'X-Content-Type-Options: nosniff'
    );


    header(
        'Cache-Control: private, no-store, no-cache, max-age=0'
    );


    header(
        'Pragma: no-cache'
    );


    /*
    |--------------------------------------------------------------------------
    | Open File
    |--------------------------------------------------------------------------
    */

    $fp = fopen(
        $path,
        'rb'
    );


    if ($fp === false) {

        exit('Unable to open file.');
    }


    /*
    |--------------------------------------------------------------------------
    | Stream File
    |--------------------------------------------------------------------------
    */

    while (!feof($fp)) {

        $buffer = fread(
            $fp,
            1024 * 1024
        );


        if ($buffer === false) {
            break;
        }


        echo $buffer;

        flush();
    }


    /*
    |--------------------------------------------------------------------------
    | Close File
    |--------------------------------------------------------------------------
    */

    fclose($fp);

    exit;


    /*
|--------------------------------------------------------------------------
| Exception Handling
|--------------------------------------------------------------------------
*/
} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    if (
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();
    }


    /*
    |--------------------------------------------------------------------------
    | Log Error
    |--------------------------------------------------------------------------
    */

    error_log(
        'Download error: ' .
            $e->getMessage() .
            ' | File: ' .
            $e->getFile() .
            ' | Line: ' .
            $e->getLine()
    );


    /*
    |--------------------------------------------------------------------------
    | Error Code
    |--------------------------------------------------------------------------
    */

    $error =
        $e->getMessage();


    /*
    |--------------------------------------------------------------------------
    | Not Found
    |--------------------------------------------------------------------------
    */

    if (
        $error === 'not_found'
    ) {

        http_response_code(404);

        exit('Sharing code not found.');
    }


    /*
    |--------------------------------------------------------------------------
    | Expired
    |--------------------------------------------------------------------------
    */

    if (
        $error === 'expired'
    ) {

        http_response_code(410);

        exit('This file has expired.');
    }


    /*
    |--------------------------------------------------------------------------
    | Download Limit
    |--------------------------------------------------------------------------
    */

    if (
        $error ===
        'download_limit_reached'
    ) {

        http_response_code(410);

        exit('This file has reached its maximum download limit.');
    }


    /*
    |--------------------------------------------------------------------------
    | Disabled
    |--------------------------------------------------------------------------
    */

    if (
        $error === 'disabled'
    ) {

        http_response_code(403);

        exit('This file is disabled.');
    }


    /*
    |--------------------------------------------------------------------------
    | Deleted
    |--------------------------------------------------------------------------
    */

    if (
        $error === 'deleted'
    ) {

        http_response_code(410);

        exit('This file is no longer available.');
    }


    /*
    |--------------------------------------------------------------------------
    | Missing Physical File
    |--------------------------------------------------------------------------
    */

    if (
        $error === 'missing'
    ) {

        http_response_code(404);

        exit('This file is no longer available.');
    }


    /*
    |--------------------------------------------------------------------------
    | Development Error
    |--------------------------------------------------------------------------
    |
    | Temporary: exact error देखने के लिए.
    | Production में इसे generic message रखें.
    |
    */

    http_response_code(500);

    echo '<h2>Download Error</h2>';

    echo '<pre>';

    echo htmlspecialchars(
        $error,
        ENT_QUOTES,
        'UTF-8'
    );

    echo "\n\nFile: ";

    echo htmlspecialchars(
        $e->getFile(),
        ENT_QUOTES,
        'UTF-8'
    );

    echo "\nLine: ";

    echo (int)$e->getLine();

    echo '</pre>';

    exit;
}
