<?php
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
foreach (['name', 'email', 'message'] as $field) {
    if (empty($input[$field])) {
        json_response(['ok' => false, 'error' => 'Please complete all required fields.'], 422);
    }
}
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

Database::insertRow('contact_messages', [
    'name'    => $input['name'],
    'email'   => $input['email'],
    'phone'   => $input['phone'] ?? null,
    'subject' => $input['subject'] ?? 'Website enquiry',
    'message' => $input['message'],
]);

NotificationSystem::broadcast('staff', 'New website enquiry', $input['name'] . ': ' . mb_substr($input['message'], 0, 120));

json_response(['ok' => true, 'message' => 'Thank you. The school office will get back to you shortly.']);
