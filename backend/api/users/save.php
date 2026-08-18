<?php
/** Creates or updates a staff/student/parent account. Only the super admin may do this. */
require_once dirname(__DIR__, 3) . '/config.php';

$actor = Auth::requirePermission('manage_users');
$input = request_input();

$id = (int) ($input['id'] ?? 0);
$role = (string) ($input['role'] ?? '');
$fullName = trim((string) ($input['full_name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));

if (!in_array($role, ['subadmin', 'cashier', 'teacher', 'student', 'parent'], true)) {
    flash('Choose a valid role.', 'error');
    redirect('/admin/users/manage.php');
}
if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('A full name and a valid email address are required.', 'error');
    redirect('/admin/users/manage.php');
}
if (Database::one('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id])) {
    flash('That email address already belongs to another account.', 'error');
    redirect('/admin/users/manage.php');
}

$fields = [
    'full_name'      => $fullName,
    'email'          => $email,
    'phone'          => $input['phone'] ?: null,
    'gender'         => in_array($input['gender'] ?? '', ['male', 'female'], true) ? $input['gender'] : null,
    'date_of_birth'  => $input['date_of_birth'] ?: null,
    'address'        => $input['address'] ?: null,
    'role'           => $role,
    'class_id'       => $input['class_id'] ? (int) $input['class_id'] : null,
    'admission_no'   => $role === 'student' ? ($input['admission_no'] ?: null) : null,
    'staff_no'       => in_array($role, ['teacher', 'cashier', 'subadmin'], true) ? ($input['staff_no'] ?: null) : null,
    'student_status' => $role === 'student' && in_array($input['student_status'] ?? '', ['new', 'returning'], true) ? $input['student_status'] : null,
    'status'         => in_array($input['status'] ?? '', ['active', 'suspended'], true) ? $input['status'] : 'active',
];

if ($id) {
    Database::updateRow('users', $id, $fields);

    if (!empty($input['password'])) {
        Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash((string) $input['password'], PASSWORD_DEFAULT), $id]);
    }
    AuditLogger::log('user.update', 'user', (string) $id, $role . ' — ' . $fullName);
    flash('Account updated.');
} else {
    $password = (string) ($input['password'] ?? '');
    if (strlen($password) < 6) {
        flash('Set a password of at least 6 characters for the new account.', 'error');
        redirect('/admin/users/manage.php');
    }
    $fields['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    $newId = Database::insertRow('users', $fields);

    foreach (Permissions::defaultsFor($role) as $permission) {
        Permissions::grant($newId, $permission, (int) $actor['id']);
    }
    AuditLogger::log('user.create', 'user', (string) $newId, $role . ' — ' . $fullName);
    flash('Account created for ' . $fullName . '.');
}

redirect('/admin/users/manage.php');
