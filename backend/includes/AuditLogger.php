<?php
declare(strict_types=1);

/** Records important actions so the super admin can oversee everything. */
final class AuditLogger
{
    public static function log(string $action, string $entity = '', string $entityId = '', string $details = ''): void
    {
        $user = $_SESSION['user'] ?? null;
        try {
            Database::run(
                'INSERT INTO audit_logs (user_id, actor_name, action, entity, entity_id, details, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $user['id'] ?? null,
                    $user['full_name'] ?? 'guest',
                    $action,
                    $entity,
                    $entityId,
                    $details,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );
        } catch (Throwable $e) {
            error_log('audit log failed: ' . $e->getMessage());
        }
    }

    public static function recent(int $limit = 100): array
    {
        return Database::all('SELECT * FROM audit_logs ORDER BY id DESC LIMIT ' . max(1, $limit));
    }
}
