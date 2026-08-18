<?php
require_once dirname(__DIR__) . '/config.php';
Auth::requireRole('cashier');

$pageTitle = 'Cashier dashboard';
$pageSubtitle = 'Approve fee payments and issue receipts';
$activeMenu = 'dashboard';

$totals = PaymentProcessor::totals();
$queue = PaymentProcessor::awaitingApproval();
$today = Database::one(
    "SELECT COALESCE(SUM(amount),0) AS total, COUNT(*) AS count
     FROM payment_transactions WHERE status = 'approved' AND DATE(approved_at) = CURDATE()"
);

require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat gold"><span class="ico">⏳</span><div><strong><?= count($queue) ?></strong><small>Waiting for approval</small></div></div>
    <div class="stat green"><span class="ico">💵</span><div><strong><?= money($today['total'] ?? 0) ?></strong><small>Approved today (<?= (int) ($today['count'] ?? 0) ?>)</small></div></div>
    <div class="stat purple"><span class="ico">✅</span><div><strong><?= money($totals['approved_total'] ?? 0) ?></strong><small>Approved this session</small></div></div>
    <div class="stat blue"><span class="ico">📅</span><div><strong><?= e(current_term()) ?> Term</strong><small><?= e(current_session_name()) ?></small></div></div>
</div>

<div class="panel mt-3" id="paymentQueue">
    <div class="panel-head">
        <h2>Payment queue</h2>
        <a class="btn btn-primary btn-sm" href="<?= url('cashier/verify-payments.php') ?>">Open approval desk</a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Received</th><th>Student</th><th>Class</th><th>Term</th><th>Channel</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($queue, 0, 10) as $payment): ?>
                <tr>
                    <td><?= pretty_date($payment['created_at'], 'd M H:i') ?></td>
                    <td><?= e($payment['student_name']) ?></td>
                    <td><?= e($payment['class_name']) ?></td>
                    <td><?= e($payment['term']) ?></td>
                    <td><?= e(ucfirst(str_replace('-', ' ', $payment['channel']))) ?></td>
                    <td><?= money($payment['amount']) ?></td>
                    <td><span class="badge badge-gold"><?= e(ucfirst($payment['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$queue): ?><tr><td colspan="7" class="muted">Nothing is waiting for approval right now.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
