<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('student', 'parent');

$pageTitle = 'Payment history';
$activeMenu = 'history';

$payments = Database::all(
    'SELECT p.*, c.name AS class_name FROM payment_transactions p
     LEFT JOIN school_classes c ON c.id = p.class_id
     WHERE p.student_id = ? OR p.payer_id = ?
     ORDER BY p.id DESC',
    [$user['id'], $user['id']]
);
$paid = array_sum(array_map(static fn ($row) => $row['status'] === 'approved' ? (float) $row['amount'] : 0, $payments));

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat green"><span class="ico">✅</span><div><strong><?= money($paid) ?></strong><small>Approved payments</small></div></div>
    <div class="stat gold"><span class="ico">⏳</span><div><strong><?= count(array_filter($payments, static fn ($row) => in_array($row['status'], ['submitted', 'verified'], true))) ?></strong><small>Awaiting the cashier</small></div></div>
    <div class="stat purple"><span class="ico">🧾</span><div><strong><?= count($payments) ?></strong><small>Payments on record</small></div></div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>All payments</h2><a class="btn btn-primary btn-sm" href="<?= url('student/payments/pay.php') ?>">Make a payment</a></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Date</th><th>Reference</th><th>Term</th><th>Class</th><th>Channel</th><th>Amount</th><th>Status</th><th>Receipt</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= pretty_date($payment['created_at']) ?></td>
                    <td><?= e($payment['reference']) ?></td>
                    <td><?= e($payment['term']) ?></td>
                    <td><?= e($payment['class_name'] ?? '—') ?></td>
                    <td><?= e(ucfirst(str_replace('-', ' ', $payment['channel']))) ?></td>
                    <td><?= money($payment['amount']) ?></td>
                    <td><span class="badge badge-<?= $payment['status'] === 'approved' ? 'green' : ($payment['status'] === 'rejected' ? 'red' : 'gold') ?>"><?= e(ucfirst($payment['status'])) ?></span></td>
                    <td><?= $payment['receipt_number'] ? e($payment['receipt_number']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$payments): ?><tr><td colspan="8" class="muted">You have not made any payments yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
