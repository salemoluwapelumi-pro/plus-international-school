<?php
/** Student self-registration — the admission number must exist in the school records. */
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
$required = ['full_name', 'admission_no', 'email', 'password', 'class_id'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        json_response(['ok' => false, 'error' => 'Please complete all required fields.'], 422);
    }
}
if (strlen((string) $input['password']) < 6) {
    json_response(['ok' => false, 'error' => 'The password must be at least 6 characters long.'], 422);
}
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

$admissionNo = trim((string) $input['admission_no']);
$existing = Database::one('SELECT * FROM users WHERE admission_no = ?', [$admissionNo]);

if (!$existing) {
    json_response([
        'ok' => false,
        'error' => 'That admission number is not in our records. Please contact the school office.',
    ], 404);
}
if (!empty($existing['password_hash']) && $existing['status'] === 'active' && $existing['last_login']) {
    json_response(['ok' => false, 'error' => 'An account already exists for this admission number. Please sign in.'], 409);
}
if (Database::one('SELECT id FROM users WHERE email = ? AND id <> ?', [$input['email'], $existing['id']])) {
    json_response(['ok' => false, 'error' => 'That email address is already in use.'], 409);
}

Database::run(
    "UPDATE users SET full_name = ?, email = ?, phone = ?, gender = ?, date_of_birth = ?, class_id = ?,
        password_hash = ?, status = 'active'
     WHERE id = ?",
    [
        $input['full_name'],
        $input['email'],
        $input['phone'] ?? null,
        in_array($input['gender'] ?? '', ['male', 'female'], true) ? $input['gender'] : null,
        $input['date_of_birth'] ?: null,
        (int) $input['class_id'],
        password_hash((string) $input['password'], PASSWORD_DEFAULT),
        $existing['id'],
    ]
);

$user = Database::one('SELECT * FROM users WHERE id = ?', [$existing['id']]);
Auth::login($user);
AuditLogger::log('student.self_register', 'user', (string) $user['id'], $admissionNo);

json_response(['ok' => true, 'redirect' => Auth::dashboardFor('student')]);
