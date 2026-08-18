<?php
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
$email = trim((string) ($input['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Enter a valid email address.'], 422);
}

$token = PasswordReset::request($email);

// The same response is returned whether or not the email exists.
$payload = ['ok' => true, 'message' => 'If that email is registered, a reset link has been sent to it.'];
if ($token && !MAIL_ENABLED) {
    $payload['reset_link'] = url('/portal/reset-password.php?token=' . $token);
}
json_response($payload);
