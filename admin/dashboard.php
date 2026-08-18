<?php
require_once dirname(__DIR__) . '/config.php';
Auth::requireRole('superadmin', 'subadmin');

$pageTitle = 'Administrator dashboard';
$activeMenu = 'dashboard';
require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';

$counts = [
    'students' => (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'student'"),
    'teachers' => (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'teacher'"),
    'parents'  => (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'parent'"),
    'classes'  => (int) Database::value('SELECT COUNT(*) FROM school_classes'),
];
$totals = PaymentProcessor::totals();
$applications = Database::all('SELECT * FROM admission_applications ORDER BY id DESC LIMIT 6');
$recentPayments = Database::all(
    'SELECT p.*, c.name AS class_name FROM payment_transactions p
     LEFT JOIN school_classes c ON c.id = p.class_id ORDER BY p.id DESC LIMIT 8'
);
$audit = AuditLogger::recent(8);
?>
<div class="stats">
    <div class="stat purple"><span class="ico">🎓</span><div><strong><?= $counts['students'] ?></strong><small>Students</small></div></div>
    <div class="stat gold"><span class="ico">👩‍🏫</span><div><strong><?= $counts['teachers'] ?></strong><small>Teachers</small></div></div>
    <div class="stat green"><span class="ico">💰</span><div><strong><?= money($totals['approved_total'] ?? 0) ?></strong><small>Fees approved</small></div></div>
    <div class="stat blue"><span class="ico">⏳</span><div><strong><?= (int) ($totals['pending_count'] ?? 0) ?></strong><small>Payments awaiting the cashier</small></div></div>
</div>

<div class="grid grid-2 mt-3">
    <div class="panel">
        <div class="panel-head"><h2>Latest payments</h2><a class="btn btn-ghost btn-sm" href="<?= url('admin/payments/overview.php') ?>">View all</a></div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Student</th><th>Class</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentPayments as $payment): ?>
                    <tr>
                        <td><?= e($payment['student_name']) ?></td>
                        <td><?= e($payment['class_name'] ?? '—') ?></td>
                        <td><?= money($payment['amount']) ?></td>
                        <td><span class="badge badge-<?= $payment['status'] === 'approved' ? 'green' : ($payment['status'] === 'rejected' ? 'red' : 'gold') ?>"><?= e(ucfirst($payment['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentPayments): ?><tr><td colspan="4" class="muted">No payments recorded yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Recent admission applications</h2><a class="btn btn-ghost btn-sm" href="<?= url('admin/system/admissions.php') ?>">View all</a></div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Child</th><th>Class</th><th>Parent</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?= e($application['child_name']) ?></td>
                        <td><?= e($application['class_applied']) ?></td>
                        <td><?= e($application['parent_name']) ?></td>
                        <td><span class="badge badge-gold"><?= e(ucfirst($application['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$applications): ?><tr><td colspan="4" class="muted">No applications yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Recent activity</h2><?php if (Auth::can('view_audit_log')): ?><a class="btn btn-ghost btn-sm" href="<?= url('admin/system/audit-log.php') ?>">Full audit log</a><?php endif; ?></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Details</th></tr></thead>
            <tbody>
            <?php foreach ($audit as $entry): ?>
                <tr>
                    <td><?= pretty_date($entry['created_at'], 'd M H:i') ?></td>
                    <td><?= e($entry['actor_name'] ?? 'System') ?></td>
                    <td><?= e($entry['action']) ?></td>
                    <td class="muted"><?= e($entry['details']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$audit): ?><tr><td colspan="4" class="muted">No activity recorded yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
