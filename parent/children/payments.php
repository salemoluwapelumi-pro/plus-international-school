<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('parent');

$pageTitle = 'Child payments';
$activeMenu = 'child-payments';

$studentId = (int) ($_GET['student_id'] ?? 0);
$linked = (int) Database::value('SELECT COUNT(*) FROM parent_students WHERE parent_id = ? AND student_id = ?', [$user['id'], $studentId]);
if (!$linked) {
    flash('That student is not linked to your account.', 'error');
    redirect('/parent/dashboard.php');
}

$student = Database::one('SELECT u.*, c.name AS class_name FROM users u LEFT JOIN school_classes c ON c.id = u.class_id WHERE u.id = ?', [$studentId]);
$payments = Database::all('SELECT * FROM payment_transactions WHERE student_id = ? ORDER BY id DESC', [$studentId]);
$expected = $student && $student['class_id'] ? PaymentProcessor::expectedFee((int) $student['class_id'], current_term(), current_session_name()) : 0.0;
$paid = (float) Database::value("SELECT COALESCE(SUM(amount),0) FROM payment_transactions WHERE student_id = ? AND status = 'approved' AND term = ? AND session_name = ?", [$studentId, current_term(), current_session_name()]);

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat green"><span class="ico">✅</span><div><strong><?= money($paid) ?></strong><small>Paid this term</small></div></div>
    <div class="stat gold"><span class="ico">🧮</span><div><strong><?= money(max(0, $expected - $paid)) ?></strong><small>Outstanding</small></div></div>
    <div class="stat purple"><span class="ico">🎓</span><div><strong><?= e($student['class_name'] ?? '—') ?></strong><small><?= e($student['full_name'] ?? '') ?></small></div></div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Payment history</h2><a class="btn btn-primary btn-sm" href="<?= url('student/payments/pay.php') ?>">Pay fees</a></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Date</th><th>Reference</th><th>Term</th><th>Channel</th><th>Amount</th><th>Status</th><th>Receipt</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= pretty_date($payment['created_at']) ?></td>
                    <td><?= e($payment['reference']) ?></td>
                    <td><?= e($payment['term']) ?></td>
                    <td><?= e(ucfirst(str_replace('-', ' ', $payment['channel']))) ?></td>
                    <td><?= money($payment['amount']) ?></td>
                    <td><span class="badge badge-<?= $payment['status'] === 'approved' ? 'green' : ($payment['status'] === 'rejected' ? 'red' : 'gold') ?>"><?= e(ucfirst($payment['status'])) ?></span></td>
                    <td><?= e($payment['receipt_number'] ?: '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$payments): ?><tr><td colspan="7" class="muted">No payments recorded for this student.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
