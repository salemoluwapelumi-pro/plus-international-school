<?php
/** Polled every few seconds by the chat UI for messages newer than `after`. */
require_once dirname(__DIR__, 3) . '/config.php';

$me = ChatSystem::user();
if (!$me) {
    json_response(['ok' => false, 'error' => 'Your chat session has expired.'], 401);
}

$peerId = (int) ($_GET['peer'] ?? 0);
$afterId = (int) ($_GET['after'] ?? 0);
if (!$peerId) {
    json_response(['ok' => false, 'error' => 'No conversation selected.'], 422);
}

ChatSystem::setStatus((int) $me['id'], 'online');
$messages = ChatSystem::conversation((int) $me['id'], $peerId, $afterId);
ChatSystem::markRead((int) $me['id'], $peerId);

json_response(['ok' => true, 'messages' => $messages]);
