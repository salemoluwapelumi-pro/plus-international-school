<?php
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::user();
if (!$user) {
    json_response(['ok' => false, 'items' => [], 'unread' => 0], 401);
}

if (($_GET['action'] ?? '') === 'read' && isset($_GET['id'])) {
    NotificationSystem::markRead((int) $_GET['id'], (int) $user['id']);
}

json_response([
    'ok' => true,
    'items' => NotificationSystem::forUser($user),
    'unread' => NotificationSystem::unreadCount($user),
]);
