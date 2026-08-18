<?php
declare(strict_types=1);

/**
 * Chat between teachers and students. The chat system has its own accounts and
 * its own login page, so it can be used independently of the school portal.
 */
final class ChatSystem
{
    public static function register(array $data): array
    {
        if (Database::one('SELECT id FROM chat_users WHERE email = ?', [$data['email']])) {
            return ['ok' => false, 'error' => 'An account with that email already exists.'];
        }
        if ($data['role'] === 'student') {
            $student = Database::one(
                "SELECT id FROM users WHERE admission_no = ? AND role = 'student'",
                [$data['admission_no'] ?? '']
            );
            if (!$student) {
                return ['ok' => false, 'error' => 'Admission number not found in the school records.'];
            }
        }
        $id = Database::insert(
            'INSERT INTO chat_users
                (user_id, full_name, email, phone, password_hash, role, admission_no, staff_no, gender, age, class_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['user_id'] ?? null,
                $data['full_name'],
                $data['email'],
                $data['phone'] ?? null,
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['admission_no'] ?? null,
                $data['staff_no'] ?? null,
                $data['gender'] ?? null,
                $data['age'] ?? null,
                $data['class_id'] ?: null,
            ]
        );
        return ['ok' => true, 'id' => $id];
    }

    public static function attempt(string $email, string $password): ?array
    {
        $user = Database::one('SELECT * FROM chat_users WHERE email = ?', [$email]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        unset($user['password_hash']);
        self::setStatus((int) $user['id'], 'online');
        $_SESSION['chat_user'] = $user;
        return $user;
    }

    public static function user(): ?array
    {
        return $_SESSION['chat_user'] ?? null;
    }

    public static function requireLogin(): array
    {
        if (empty($_SESSION['chat_user'])) {
            redirect('/chat/login.php');
        }
        return $_SESSION['chat_user'];
    }

    public static function setStatus(int $userId, string $status): void
    {
        Database::run('UPDATE chat_users SET chat_status = ?, last_seen = NOW() WHERE id = ?', [$status, $userId]);
    }

    /** Everyone the current user may chat with, newest conversations first. */
    public static function contacts(int $userId): array
    {
        return Database::all(
            'SELECT cu.id, cu.full_name, cu.role, cu.chat_status, cu.class_id, c.name AS class_name,
                    (SELECT body FROM chat_messages m
                      WHERE (m.sender_id = cu.id AND m.receiver_id = ?)
                         OR (m.sender_id = ? AND m.receiver_id = cu.id)
                      ORDER BY m.id DESC LIMIT 1) AS last_message,
                    (SELECT COUNT(*) FROM chat_messages m
                      WHERE m.sender_id = cu.id AND m.receiver_id = ? AND m.is_read = 0) AS unread
             FROM chat_users cu
             LEFT JOIN school_classes c ON c.id = cu.class_id
             WHERE cu.id <> ?
             ORDER BY unread DESC, cu.full_name',
            [$userId, $userId, $userId, $userId]
        );
    }

    public static function conversation(int $userId, int $peerId, int $afterId = 0): array
    {
        return Database::all(
            'SELECT * FROM chat_messages
             WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
               AND id > ?
             ORDER BY id ASC LIMIT 200',
            [$userId, $peerId, $peerId, $userId, $afterId]
        );
    }

    public static function send(int $senderId, int $receiverId, string $body): int
    {
        $id = Database::insert(
            'INSERT INTO chat_messages (sender_id, receiver_id, body) VALUES (?,?,?)',
            [$senderId, $receiverId, $body]
        );
        return $id;
    }

    public static function markRead(int $userId, int $peerId): void
    {
        Database::run(
            'UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?',
            [$userId, $peerId]
        );
    }
}
