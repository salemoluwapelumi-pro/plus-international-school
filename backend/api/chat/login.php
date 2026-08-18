<?php
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
$user = ChatSystem::attempt(trim((string) ($input['email'] ?? '')), (string) ($input['password'] ?? ''));

if (!$user) {
    json_response(['ok' => false, 'error' => 'Invalid email address or password.'], 401);
}

json_response(['ok' => true, 'redirect' => url('/chat/app/index.php')]);
