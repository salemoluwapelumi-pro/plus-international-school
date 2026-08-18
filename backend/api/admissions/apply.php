<?php
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
foreach (['child_name', 'class_applied', 'parent_name', 'email', 'phone'] as $field) {
    if (empty($input[$field])) {
        json_response(['ok' => false, 'error' => 'Please complete all required fields.'], 422);
    }
}
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

$reference = 'APP/' . date('Y') . '/' . strtoupper(bin2hex(random_bytes(3)));

Database::insertRow('admission_applications', [
    'reference'      => $reference,
    'child_name'     => $input['child_name'],
    'date_of_birth'  => $input['date_of_birth'] ?: null,
    'gender'         => in_array($input['gender'] ?? '', ['male', 'female'], true) ? $input['gender'] : null,
    'class_applied'  => $input['class_applied'],
    'parent_name'    => $input['parent_name'],
    'email'          => $input['email'],
    'phone'          => $input['phone'],
    'address'        => $input['address'] ?? null,
    'previous_school' => $input['previous_school'] ?? null,
    'status'         => 'pending',
]);

NotificationSystem::broadcast(
    'staff',
    'New admission application',
    $input['child_name'] . ' applied for ' . $input['class_applied'] . ' (' . $reference . ')'
);

json_response([
    'ok' => true,
    'reference' => $reference,
    'message' => 'Application received. Your reference number is ' . $reference . '. The admissions office will contact you.',
]);
