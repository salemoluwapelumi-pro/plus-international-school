<?php
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
$role = in_array($input['role'] ?? '', ['student', 'teacher'], true) ? $input['role'] : '';

foreach (['full_name', 'email', 'phone', 'password'] as $field) {
    if (empty($input[$field])) {
        json_response(['ok' => false, 'error' => 'Please complete all required fields.'], 422);
    }
}
if ($role === '') {
    json_response(['ok' => false, 'error' => 'Choose whether you are a student or a teacher.'], 422);
}
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}
if (strlen((string) $input['password']) < 6) {
    json_response(['ok' => false, 'error' => 'The password must be at least 6 characters long.'], 422);
}
if ($role === 'student' && empty($input['admission_no'])) {
    json_response(['ok' => false, 'error' => 'Students must supply their admission number.'], 422);
}

$result = ChatSystem::register([
    'full_name'    => $input['full_name'],
    'email'        => $input['email'],
    'phone'        => $input['phone'],
    'password'     => $input['password'],
    'role'         => $role,
    'admission_no' => $input['admission_no'] ?? null,
    'staff_no'     => $input['staff_no'] ?? null,
    'gender'       => in_array($input['gender'] ?? '', ['male', 'female'], true) ? $input['gender'] : null,
    'age'          => $input['age'] ? (int) $input['age'] : null,
    'class_id'     => $input['class_id'] ?? null,
]);

if (!$result['ok']) {
    json_response($result, 422);
}

ChatSystem::attempt((string) $input['email'], (string) $input['password']);
json_response(['ok' => true, 'redirect' => url('/chat/app/index.php')]);
