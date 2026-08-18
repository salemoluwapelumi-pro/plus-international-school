<?php
/** Every signed-in user can update their own contact details and password here. */
require_once dirname(__DIR__) . '/config.php';
$user = Auth::requireLogin();

$pageTitle = 'My profile';
$activeMenu = 'profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'full_name' => trim((string) $_POST['full_name']),
        'email'     => trim((string) $_POST['email']),
        'phone'     => trim((string) $_POST['phone']),
        'address'   => trim((string) $_POST['address']),
    ];
    Database::updateRow('users', (int) $user['id'], $fields);

    $password = (string) ($_POST['password'] ?? '');
    if ($password !== '') {
        if (strlen($password) < 6 || $password !== ($_POST['password_confirm'] ?? '')) {
            flash('The new password must be at least 6 characters and both entries must match.', 'error');
            redirect('/portal/profile.php');
        }
        Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    AuditLogger::log('profile.update', 'user', (string) $user['id']);
    flash('Profile updated.');
    redirect('/portal/profile.php');
}

$user = Database::one('SELECT * FROM users WHERE id = ?', [$user['id']]);
require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="grid grid-2">
    <div class="panel">
        <div class="panel-head"><h2>My details</h2><span class="badge badge-purple"><?= e(ucfirst($user['role'])) ?></span></div>
        <form method="post">
            <div class="form-grid">
                <div class="field field-full"><label for="full_name">Full name</label><input id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required></div>
                <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($user['email']) ?>" required></div>
                <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>"></div>
                <div class="field field-full"><label for="address">Address</label><input id="address" name="address" value="<?= e($user['address'] ?? '') ?>"></div>
                <div class="field"><label for="password">New password</label><input id="password" type="password" name="password" autocomplete="new-password"></div>
                <div class="field"><label for="password_confirm">Confirm new password</label><input id="password_confirm" type="password" name="password_confirm" autocomplete="new-password"></div>
            </div>
            <button class="btn btn-primary" type="submit">Save changes</button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Account</h2></div>
        <ul class="plain-list">
            <li><span class="muted">Role</span><br><strong><?= e(ucfirst($user['role'])) ?></strong></li>
            <?php if ($user['admission_no']): ?><li><span class="muted">Admission number</span><br><strong><?= e($user['admission_no']) ?></strong></li><?php endif; ?>
            <?php if ($user['staff_no']): ?><li><span class="muted">Staff number</span><br><strong><?= e($user['staff_no']) ?></strong></li><?php endif; ?>
            <li><span class="muted">Status</span><br><strong><?= e(ucfirst($user['status'])) ?></strong></li>
            <li><span class="muted">Member since</span><br><strong><?= pretty_date($user['created_at']) ?></strong></li>
        </ul>
        <?php if (in_array($user['role'], ['teacher', 'student'], true)): ?>
            <a class="btn btn-gold btn-block mt-2" href="<?= url('chat/login.php') ?>">Open the chat system</a>
        <?php endif; ?>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
