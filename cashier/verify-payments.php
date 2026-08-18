<?php
/** The approval desk: the cashier alone approves payments and issues receipts. */
require_once dirname(__DIR__) . '/config.php';
Auth::requirePermission('approve_payments');

$pageTitle = 'Approve payments';
$pageSubtitle = 'New payments appear here the moment a student or parent pays';
$activeMenu = 'verify';

$queue = PaymentProcessor::awaitingApproval();
require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel" id="paymentQueue">
    <div class="panel-head"><h2>Awaiting approval (<?= count($queue) ?>)</h2><input class="table-search" data-table-search="#queueTable" placeholder="Search by student or reference"></div>
    <div class="table-wrap">
        <table class="data" id="queueTable">
            <thead><tr><th>Received</th><th>Reference</th><th>Student</th><th>Class</th><th>Term</th><th>Channel</th><th>Expected</th><th>Paid</th><th>Proof</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($queue as $payment): ?>
                <tr>
                    <td><?= pretty_date($payment['created_at'], 'd M H:i') ?></td>
                    <td><?= e($payment['reference']) ?></td>
                    <td><?= e($payment['student_name']) ?><br><small class="muted"><?= e(ucfirst($payment['student_status'] ?? 'new')) ?> student</small></td>
                    <td><?= e($payment['class_name']) ?></td>
                    <td><?= e($payment['term']) ?></td>
                    <td><?= e(ucfirst(str_replace('-', ' ', $payment['channel']))) ?></td>
                    <td><?= money($payment['amount_expected']) ?></td>
                    <td><strong><?= money($payment['amount']) ?></strong></td>
                    <td><?= $payment['proof_path'] ? '<a target="_blank" href="' . e(url($payment['proof_path'])) . '">View</a>' : '—' ?></td>
                    <td><span class="badge badge-<?= $payment['status'] === 'verified' ? 'blue' : 'gold' ?>"><?= e(ucfirst($payment['status'])) ?></span></td>
                    <td class="actions">
                        <?php if ($payment['status'] === 'submitted'): ?>
                            <form method="post" action="<?= url('backend/api/payments/verify.php') ?>">
                                <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                                <button class="btn btn-ghost btn-sm" type="submit">Verify</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= url('backend/api/payments/approve.php') ?>" data-confirm="Approve <?= e($payment['student_name']) ?>'s payment of <?= e(money($payment['amount'])) ?> and issue a receipt?">
                            <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                            <button class="btn btn-primary btn-sm" type="submit">Approve</button>
                        </form>
                        <form method="post" action="<?= url('backend/api/payments/reject.php') ?>" data-confirm="Reject this payment?">
                            <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                            <input type="hidden" name="note" value="Payment could not be confirmed by the cashier.">
                            <button class="btn btn-danger btn-sm" type="submit">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$queue): ?><tr><td colspan="11" class="muted">The queue is empty — every payment has been dealt with.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
