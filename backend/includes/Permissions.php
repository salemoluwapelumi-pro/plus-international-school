<?php
declare(strict_types=1);

/**
 * Granular permission management.
 * The super admin implicitly holds every permission and is the only role that
 * can grant permissions to other users.
 */
final class Permissions
{
    public const ALL = [
        'manage_users'        => 'Create, edit and delete staff / student accounts',
        'manage_permissions'  => 'Grant or revoke permissions',
        'view_payments'       => 'View submitted payments',
        'approve_payments'    => 'Verify and approve payments (money access)',
        'manage_fees'         => 'Manage the fee structure and bank accounts',
        'manage_results'      => 'Upload, edit and publish results',
        'manage_records'      => 'Add, edit and delete permanent student records',
        'manage_timetable'    => 'Create and edit the weekly timetable',
        'view_timetable'      => 'View the full school timetable',
        'manage_announcements'=> 'Publish announcements',
        'view_reports'        => 'View financial and academic reports',
        'view_audit_log'      => 'View the system audit log',
    ];

    /** Permissions every holder of a role gets automatically. */
    private const ROLE_DEFAULTS = [
        'superadmin' => ['*'],
        'subadmin'   => ['view_payments', 'view_timetable', 'view_reports'],
        'cashier'    => ['view_payments', 'approve_payments'],
        'teacher'    => ['manage_results', 'view_timetable'],
        'student'    => [],
        'parent'     => [],
    ];

    public static function defaultsFor(string $role): array
    {
        return self::ROLE_DEFAULTS[$role] ?? [];
    }

    public static function granted(int $userId): array
    {
        $rows = Database::all(
            'SELECT permission FROM user_permissions
             WHERE user_id = ? AND (expires_at IS NULL OR expires_at > NOW())',
            [$userId]
        );
        return array_column($rows, 'permission');
    }

    public static function forUser(array $user): array
    {
        if ($user['role'] === 'superadmin') {
            return array_keys(self::ALL);
        }
        return array_values(array_unique(array_merge(self::defaultsFor($user['role']), self::granted((int) $user['id']))));
    }

    public static function grant(int $userId, string $permission, int $grantedBy, ?string $expiresAt = null): void
    {
        Database::run(
            'INSERT INTO user_permissions (user_id, permission, granted_by, expires_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE granted_by = VALUES(granted_by), expires_at = VALUES(expires_at)',
            [$userId, $permission, $grantedBy, $expiresAt]
        );
        AuditLogger::log('permission.grant', 'user', (string) $userId, $permission);
    }

    public static function revoke(int $userId, string $permission): void
    {
        Database::run('DELETE FROM user_permissions WHERE user_id = ? AND permission = ?', [$userId, $permission]);
        AuditLogger::log('permission.revoke', 'user', (string) $userId, $permission);
    }
}
