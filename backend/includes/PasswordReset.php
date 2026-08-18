<?php
declare(strict_types=1);

/** Email-based "forgot password" flow. */
final class PasswordReset
{
    public static function request(string $email): ?string
    {
        $user = Database::one('SELECT id, full_name FROM users WHERE email = ?', [$email]);
        if (!$user) {
            return null;
        }
        $token = bin2hex(random_bytes(24));
        Database::run(
            'UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?',
            [hash('sha256', $token), $user['id']]
        );
        $link = url('/portal/reset-password.php?token=' . $token);

        if (MAIL_ENABLED) {
            @mail(
                $email,
                SCHOOL_NAME . ' — password reset',
                "Hello {$user['full_name']},\n\nUse this link to reset your password (valid for 1 hour):\n$link\n",
                'From: ' . MAIL_FROM
            );
        }
        AuditLogger::log('password.reset_requested', 'user', (string) $user['id']);
        return $token;
    }

    public static function userForToken(string $token): ?array
    {
        return Database::one(
            'SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()',
            [hash('sha256', $token)]
        );
    }

    public static function complete(string $token, string $password): bool
    {
        $user = self::userForToken($token);
        if (!$user) {
            return false;
        }
        Database::run(
            'UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $user['id']]
        );
        AuditLogger::log('password.reset_completed', 'user', (string) $user['id']);
        return true;
    }
}
