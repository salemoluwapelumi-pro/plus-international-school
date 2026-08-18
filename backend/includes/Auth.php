<?php
declare(strict_types=1);

/**
 * Authentication for the single shared portal login.
 * Every role (superadmin, subadmin, cashier, teacher, student, parent) signs in
 * through the same form; the role is resolved from the database afterwards and
 * decides which dashboard the user lands on.
 */
final class Auth
{
    public const DASHBOARDS = [
        'superadmin' => '/admin/dashboard.php',
        'subadmin'   => '/admin/dashboard.php',
        'cashier'    => '/cashier/dashboard.php',
        'teacher'    => '/teacher/dashboard.php',
        'student'    => '/student/dashboard.php',
        'parent'     => '/parent/dashboard.php',
    ];

    public static function attempt(string $identifier, string $password): ?array
    {
        $user = Database::one(
            'SELECT * FROM users WHERE email = ? OR admission_no = ? OR staff_no = ? LIMIT 1',
            [$identifier, $identifier, $identifier]
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            AuditLogger::log('login.failed', 'user', $identifier);
            return null;
        }
        if ($user['status'] !== 'active') {
            return null;
        }
        Database::run('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);
        self::login($user);
        AuditLogger::log('login.success', 'user', (string) $user['id'], $user['role']);
        return $user;
    }

    public static function login(array $user): void
    {
        unset($user['password_hash'], $user['reset_token']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $_SESSION['permissions'] = Permissions::forUser($user);
    }

    public static function logout(): void
    {
        AuditLogger::log('logout');
        $_SESSION = [];
        session_destroy();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function role(): string
    {
        return $_SESSION['user']['role'] ?? '';
    }

    public static function is(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function can(string $permission): bool
    {
        if (self::role() === 'superadmin') {
            return true;
        }
        return in_array($permission, $_SESSION['permissions'] ?? [], true);
    }

    /** Server-side counterpart of redirectToDashboard(role) in assets/js/auth.js. */
    public static function dashboardFor(string $role): string
    {
        return url(self::DASHBOARDS[$role] ?? '/portal/index.php');
    }

    public static function requireLogin(): array
    {
        if (!self::check()) {
            header('Location: ' . url('/portal/login.php'));
            exit;
        }
        return self::user();
    }

    public static function requireRole(string ...$roles): array
    {
        $user = self::requireLogin();
        if (!self::is(...$roles)) {
            http_response_code(403);
            exit('Access denied.');
        }
        return $user;
    }

    public static function requirePermission(string $permission): array
    {
        $user = self::requireLogin();
        if (!self::can($permission)) {
            http_response_code(403);
            exit('You do not have permission to view this page.');
        }
        return $user;
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function checkCsrf(?string $token): bool
    {
        return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }
}
