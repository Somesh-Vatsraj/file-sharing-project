# ShareVault — PHP File Sharing

A deployable PC-to-PC file sharing web application built with PHP 8.2+, MySQL 8+, PDO, HTML5, CSS3 and Vanilla JavaScript.

## Requirements

- PHP 8.2+
- MySQL 8+
- PDO + PDO_MySQL
- Fileinfo
- JSON
- Mbstring
- Apache with `.htaccess` support (recommended)
- HTTPS strongly recommended

## Installation

1. Create a MySQL database, for example `file_sharing`.
2. Import `database.sql`.
3. Edit `config/config.php` with your database host, database name, user and password.
4. Ensure `storage/files` is writable by PHP (normally 0750/0755 depending on hosting).
5. Confirm Apache/PHP upload limits are large enough for your configured maximum.
6. Open `admin/login.php` after creating the first admin account (see below).
7. Configure the website, file size, extension allowlist, expiry and download limit from Settings.
8. Set a strong database password and use HTTPS.

## First admin account

Generate a password hash using a temporary PHP CLI command:

```php
<?php echo password_hash('CHANGE_THIS_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;
```

Then insert it:

```sql
INSERT INTO admins (username, email, password)
VALUES ('admin', 'admin@example.com', 'PASTE_HASH_HERE');
```

Delete the temporary script after use.

## PHP upload settings

For larger files, check:

```ini
upload_max_filesize = 100M
post_max_size = 105M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

The application cannot bypass server-level upload limits.

## Cleanup cron

Recommended cron:

```bash
php /home/USER/public_html/file-sharing/cron_cleanup.php
```

The cleanup script marks expired files and removes physical files. If `auto_delete_expired` is enabled, expired database rows are removed as well.

## Security model

- Files use random physical storage names.
- Storage is denied by `.htaccess`.
- Download requests accept only a sharing code.
- Database access uses PDO prepared statements.
- Uploads use an extension allowlist and server-side MIME detection.
- Dangerous executable/server-side extensions are blocked.
- Admin actions use session authentication and CSRF tokens.
- Login, lookup, upload and download attempts have basic throttling.
- Download count increments happen under a database row lock to avoid concurrent-limit races.
- Production errors should be logged server-side and not displayed to visitors.
- HTTPS should be enabled on production hosting.

## cPanel notes

If your cPanel account cannot place `storage` outside `public_html`, keep the included `.htaccess` inside `storage/files`. For stronger isolation, place storage above the document root and update `STORAGE_PATH` in `config/config.php`.

## XAMPP/WAMP

Place the project under the web root, import the SQL, update database credentials and ensure the PHP `fileinfo` extension is enabled.

## Important production hardening

- Use HTTPS.
- Use a dedicated database user with only required permissions.
- Keep PHP and MySQL patched.
- Configure backups.
- Review Apache/Nginx rules for your hosting environment.
- Do not enable arbitrary custom CSS/HTML from untrusted administrators.
- Set appropriate OS permissions on storage.
