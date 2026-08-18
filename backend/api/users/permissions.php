<?php
/** Only the super admin grants or revokes permissions. */
require_once dirname(__DIR__, 3) . '/config.php';

$actor = Auth::requireRole('superadmin');
$input = request_input();
$userId = (int) ($input['user_id'] ?? 0);
$granted = (array) ($input['permissions'] ?? []);

$user = Database::one('SELECT * FROM users WHERE id = ?', [$userId]);
if (!$user) {
    flash('That account no longer exists.', 'error');
    redirect('/admin/users/permissions.php');
}

foreach (array_keys(Permissions::ALL) as $permission) {
    if (in_array($permission, $granted, true)) {
        Permissions::grant($userId, $permission, (int) $actor['id']);
    } else {
        Permissions::revoke($userId, $permission);
    }
}

AuditLogger::log('permissions.update', 'user', (string) $userId, implode(', ', $granted));
flash('Permissions updated for ' . $user['full_name'] . '.');
redirect('/admin/users/permissions.php?user_id=' . $userId);
