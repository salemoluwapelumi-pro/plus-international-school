<?php
require_once dirname(__DIR__, 3) . '/config.php';

$actor = Auth::requireRole('superadmin');
$id = (int) (request_input()['id'] ?? 0);

if ($id === (int) $actor['id']) {
    flash('You cannot delete your own account.', 'error');
    redirect('/admin/users/manage.php');
}

$user = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
if (!$user) {
    flash('That account no longer exists.', 'warning');
    redirect('/admin/users/manage.php');
}

Database::run('DELETE FROM users WHERE id = ?', [$id]);
AuditLogger::log('user.delete', 'user', (string) $id, $user['role'] . ' — ' . $user['full_name']);
flash('Account deleted.');
redirect('/admin/users/manage.php');
