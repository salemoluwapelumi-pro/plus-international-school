<?php
require_once dirname(__DIR__, 3) . '/config.php';

$me = ChatSystem::user();
if (!$me) {
    json_response(['ok' => false, 'error' => 'Your chat session has expired.'], 401);
}

$input = request_input();
$receiverId = (int) ($input['receiver_id'] ?? 0);
$body = trim((string) ($input['body'] ?? ''));

if (!$receiverId || $body === '') {
    json_response(['ok' => false, 'error' => 'Choose a contact and type a message.'], 422);
}

$id = ChatSystem::send((int) $me['id'], $receiverId, $body);
$message = Database::one('SELECT * FROM chat_messages WHERE id = ?', [$id]);

json_response(['ok' => true, 'message' => $message]);
