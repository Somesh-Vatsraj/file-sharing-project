<?php

declare(strict_types=1);
$page_title = 'Settings';
require __DIR__ . '/includes/header.php';
$pdo = db();
$saved = false;
$keys = ['website_name', 'website_logo', 'favicon', 'website_description', 'contact_email', 'contact_phone', 'footer_text', 'primary_color', 'secondary_color', 'background_color', 'border_radius', 'theme', 'hero_heading', 'hero_paragraph', 'hero_send_text', 'hero_receive_text', 'max_file_size_mb', 'allowed_extensions', 'max_downloads', 'code_length', 'code_expiry_hours', 'auto_delete_expired', 'maintenance_mode', 'accounts_required', 'upload_enabled', 'download_enabled', 'custom_css', 'how_steps', 'features', 'faq_items'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($keys as $k) {
        if (array_key_exists($k, $_POST)) {
            $v = is_string($_POST[$k]) ? trim($_POST[$k]) : '';
            $pdo->prepare("INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$k, $v]);
        }
    }
    $saved = true;
}
?><section class="panel"><?php if ($saved): ?><div class="save-ok">Settings saved successfully.</div><?php endif; ?><form method="post"><?= csrf_field() ?><div class="settings-grid"><label>Website Name<input name="website_name" value="<?= e((string)setting('website_name', 'ShareVault')) ?>"></label><label>Contact Email<input type="email" name="contact_email" value="<?= e((string)setting('contact_email', '')) ?>"></label><label>Contact Phone<input name="contact_phone" value="<?= e((string)setting('contact_phone', '')) ?>"></label><label>Footer Text<input name="footer_text" value="<?= e((string)setting('footer_text', '')) ?>"></label><label>Logo URL<input name="website_logo" value="<?= e((string)setting('website_logo', '')) ?>"></label><label>Favicon URL<input name="favicon" value="<?= e((string)setting('favicon', '')) ?>"></label><label class="wide">Description<textarea name="website_description"><?= e((string)setting('website_description', '')) ?></textarea></label><label>Primary Color<input name="primary_color" value="<?= e((string)setting('primary_color', '#7c3aed')) ?>"></label><label>Secondary Color<input name="secondary_color" value="<?= e((string)setting('secondary_color', '#2563eb')) ?>"></label><label>Background Color<input name="background_color" value="<?= e((string)setting('background_color', '#070b16')) ?>"></label><label>Border Radius<input name="border_radius" value="<?= e((string)setting('border_radius', '18px')) ?>"></label><label>Theme<select name="theme">
                    <option value="dark" <?= setting('theme', 'dark') === 'dark' ? 'selected' : '' ?>>Dark</option>
                    <option value="light" <?= setting('theme', 'dark') === 'light' ? 'selected' : '' ?>>Light</option>
                </select></label><label>Hero Heading<input name="hero_heading" value="<?= e((string)setting('hero_heading', '')) ?>"></label><label class="wide">Hero Paragraph<textarea name="hero_paragraph"><?= e((string)setting('hero_paragraph', '')) ?></textarea></label><label>Send Button Text<input name="hero_send_text" value="<?= e((string)setting('hero_send_text', 'Send File')) ?>"></label><label>Receive Button Text<input name="hero_receive_text" value="<?= e((string)setting('hero_receive_text', 'Receive File')) ?>"></label><label>Max File Size (MB)<input type="number" min="1" name="max_file_size_mb" value="<?= e((string)setting('max_file_size_mb', '100')) ?>"></label><label>Max Downloads<input type="number" min="1" name="max_downloads" value="<?= e((string)setting('max_downloads', '5')) ?>"></label><label>Code Length (6–12)<input type="number" min="6" max="12" name="code_length" value="<?= e((string)setting('code_length', '6')) ?>"></label><label>Expiry Hours<input type="number" min="1" name="code_expiry_hours" value="<?= e((string)setting('code_expiry_hours', '24')) ?>"></label><label>Allowed Extensions<input name="allowed_extensions" value="<?= e((string)setting('allowed_extensions', ALLOWED_DEFAULT_EXTENSIONS)) ?>"></label><label>Auto Delete Expired<select name="auto_delete_expired">
                    <option value="0" <?= setting('auto_delete_expired', '0') === '0' ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= setting('auto_delete_expired', '0') === '1' ? 'selected' : '' ?>>Yes</option>
                </select></label><label>Maintenance Mode<select name="maintenance_mode">
                    <option value="0" <?= setting('maintenance_mode', '0') === '0' ? 'selected' : '' ?>>Off</option>
                    <option value="1" <?= setting('maintenance_mode', '0') === '1' ? 'selected' : '' ?>>On</option>
                </select></label><label>Uploads<select name="upload_enabled">
                    <option value="1" <?= setting('upload_enabled', '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= setting('upload_enabled', '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select></label><label>Downloads<select name="download_enabled">
                    <option value="1" <?= setting('download_enabled', '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= setting('download_enabled', '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select></label></div><label class="wide">How It Works JSON<textarea name="how_steps"><?= e((string)setting('how_steps', '[]')) ?></textarea></label><label class="wide">Features JSON<textarea name="features"><?= e((string)setting('features', '[]')) ?></textarea></label><label class="wide">FAQ JSON<textarea name="faq_items"><?= e((string)setting('faq_items', '[]')) ?></textarea></label><label class="wide">Custom CSS<textarea name="custom_css"><?= e((string)setting('custom_css', '')) ?></textarea></label><button class="admin-btn" type="submit">Save Settings</button></form>
</section><?php require __DIR__ . '/includes/footer.php'; ?>