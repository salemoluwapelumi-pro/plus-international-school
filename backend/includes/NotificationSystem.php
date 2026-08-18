<?php
declare(strict_types=1);

/** In-app notifications for users and broadcast audiences. */
final class NotificationSystem
{
    public static function send(int $userId, string $title, string $body = ''): void
    {
        Database::run('INSERT INTO notifications (user_id, title, body) VALUES (?,?,?)', [$userId, $title, $body]);
    }

    public static function broadcast(string $audience, string $title, string $body = ''): void
    {
        Database::run('INSERT INTO notifications (audience, title, body) VALUES (?,?,?)', [$audience, $title, $body]);
    }

    public static function forUser(array $user, int $limit = 20): array
    {
        $audience = match ($user['role']) {
            'student' => 'student',
            'teacher' => 'teacher',
            'parent'  => 'parent',
            default   => 'staff',
        };
        return Database::all(
            'SELECT * FROM notifications
             WHERE user_id = ? OR audience = ? OR audience = ?
             ORDER BY id DESC LIMIT ' . max(1, $limit),
            [$user['id'], $audience, 'all']
        );
    }

    public static function unreadCount(array $user): int
    {
        return (int) Database::value(
            'SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND (user_id = ? OR audience = ?)',
            [$user['id'], 'all']
        );
    }

    public static function markRead(int $id, int $userId): void
    {
        Database::run('UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)', [$id, $userId]);
    }
}
