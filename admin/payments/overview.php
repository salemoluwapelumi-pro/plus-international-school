<?php
/** Administrator oversight of every payment. Approval itself is the cashier's job. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('view_payments');

$pageTitle = 'Payments';
$pageSubtitle = 'Oversight of every fee payment — the cashier approves, the administrator oversees';
$activeMenu = 'payments';

$status = $_GET['status'] ?? '';
$sql = 'SELECT p.*, c.name AS class_name, u.full_name AS cashier_name
        FROM payment_transactions p
        LEFT JOIN school_classes c ON c.id = p.class_id
        LEFT JOIN users u ON u.id = p.approver_id';
$params = [];
if (in_array($status, ['submitted', 'verified', 'approved', 'rejected'], true)) {
    $sql .= ' WHERE p.status = ?';
    $params[] = $status;
}
$sql .= ' ORDER BY p.id DESC LIMIT 300';
$payments = Database::all($sql, $params);
$totals = PaymentProcessor::totals();

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat green"><span class="ico">✅</span><div><strong><?= money($totals['approved_total'] ?? 0) ?></strong><small>Approved (<?= (int) ($totals['approved_count'] ?? 0) ?>)</small></div></div>
    <div class="stat gold"><span class="ico">⏳</span><div><strong><?= money($totals['pending_total'] ?? 0) ?></strong><small>Awaiting the cashier (<?= (int) ($totals['pending_count'] ?? 0) ?>)</small></div></div>
    <div class="stat purple"><span class="ico">🏫</span><div><strong><?= (int) Database::value('SELECT COUNT(DISTINCT class_id) FROM payment_transactions') ?></strong><small>Classes with payments</small></div></div>
    <div class="stat blue"><span class="ico">📅</span><div><strong><?= e(current_term()) ?> Term</strong><small><?= e(current_session_name()) ?></small></div></div>
</div>

<div class="panel mt-3">
    <div class="panel-head">
        <h2>All payments</h2>
        <form method="get" class="flex gap-1">
            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php foreach (['submitted' => 'Submitted', 'verified' => 'Verified', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <input class="table-search" data-table-search="#paymentsTable" placeholder="Search">
        </form>
    </div>
    <div class="table-wrap">
        <table class="data" id="paymentsTable">
            <thead><tr><th>Date</th><th>Reference</th><th>Student</th><th>Class</th><th>Term</th><th>Channel</th><th>Amount</th><th>Status</th><th>Receipt</th><th>Approved by</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= pretty_date($payment['created_at'], 'd M Y') ?></td>
                    <td><?= e($payment['reference']) ?></td>
                    <td><?= e($payment['student_name']) ?></td>
                    <td><?= e($payment['class_name'] ?? '—') ?></td>
                    <td><?= e($payment['term']) ?></td>
                    <td><?= e(ucfirst(str_replace('-', ' ', $payment['channel']))) ?></td>
                    <td><?= money($payment['amount']) ?></td>
                    <td><span class="badge badge-<?= $payment['status'] === 'approved' ? 'green' : ($payment['status'] === 'rejected' ? 'red' : 'gold') ?>"><?= e(ucfirst($payment['status'])) ?></span></td>
                    <td><?= e($payment['receipt_number'] ?: '—') ?></td>
                    <td><?= e($payment['cashier_name'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$payments): ?><tr><td colspan="10" class="muted">No payments recorded yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
