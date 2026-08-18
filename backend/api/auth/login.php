<?php
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
$identifier = trim((string) ($input['identifier'] ?? ''));
$password = (string) ($input['password'] ?? '');

if ($identifier === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Enter your login details.'], 422);
}

$user = Auth::attempt($identifier, $password);
if (!$user) {
    json_response(['ok' => false, 'error' => 'Invalid login details, or your account has been suspended.'], 401);
}

json_response([
    'ok' => true,
    'user' => [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
    ],
    'redirect' => Auth::dashboardFor($user['role']),
]);
