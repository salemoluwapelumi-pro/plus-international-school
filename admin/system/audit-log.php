<?php
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('view_audit_log');

$pageTitle = 'Audit log';
$pageSubtitle = 'Everything that happens in the system, so the administrator can oversee it all';
$activeMenu = 'audit';

$entries = AuditLogger::recent(300);
require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Activity (<?= count($entries) ?>)</h2><input class="table-search" data-table-search="#auditTable" placeholder="Search the log"></div>
    <div class="table-wrap">
        <table class="data" id="auditTable">
            <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Entity</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= pretty_date($entry['created_at'], 'd M Y H:i') ?></td>
                    <td><?= e($entry['actor_name']) ?></td>
                    <td><span class="badge badge-purple"><?= e($entry['action']) ?></span></td>
                    <td><?= e($entry['entity'] ?: '—') ?> <?= e($entry['entity_id'] ?: '') ?></td>
                    <td class="muted"><?= e($entry['details']) ?></td>
                    <td class="muted"><?= e($entry['ip_address'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$entries): ?><tr><td colspan="6" class="muted">Nothing logged yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
