<?php
/** School-wide settings stored in the settings table as simple key/value pairs. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requireRole('superadmin');

$pageTitle = 'Settings';
$activeMenu = 'settings';

$defaults = [
    'school_phone'     => SCHOOL_PHONE,
    'school_email'     => SCHOOL_EMAIL,
    'school_address'   => SCHOOL_ADDRESS,
    'principal_name'   => '',
    'results_locked'   => '0',
    'payment_notice'   => 'Fees may be paid online with Paystack or Remita, or at the school bursary.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($defaults) as $key) {
        Database::run(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, (string) ($_POST[$key] ?? '')]
        );
    }
    AuditLogger::log('settings.update');
    flash('Settings saved.');
    redirect('/admin/system/settings.php');
}

$settings = $defaults;
foreach (Database::all('SELECT * FROM settings') as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>School settings</h2></div>
    <form method="post">
        <div class="form-grid">
            <div class="field"><label for="school_phone">Phone number</label><input id="school_phone" name="school_phone" value="<?= e($settings['school_phone']) ?>"></div>
            <div class="field"><label for="school_email">Email address</label><input id="school_email" name="school_email" value="<?= e($settings['school_email']) ?>"></div>
            <div class="field field-full"><label for="school_address">Address</label><input id="school_address" name="school_address" value="<?= e($settings['school_address']) ?>"></div>
            <div class="field"><label for="principal_name">Principal</label><input id="principal_name" name="principal_name" value="<?= e($settings['principal_name']) ?>"></div>
            <div class="field">
                <label for="results_locked">Result entry</label>
                <select id="results_locked" name="results_locked">
                    <option value="0" <?= $settings['results_locked'] === '0' ? 'selected' : '' ?>>Open to teachers</option>
                    <option value="1" <?= $settings['results_locked'] === '1' ? 'selected' : '' ?>>Locked</option>
                </select>
            </div>
            <div class="field field-full"><label for="payment_notice">Payment notice</label><textarea id="payment_notice" name="payment_notice" rows="3"><?= e($settings['payment_notice']) ?></textarea></div>
        </div>
        <button class="btn btn-primary" type="submit">Save settings</button>
    </form>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
