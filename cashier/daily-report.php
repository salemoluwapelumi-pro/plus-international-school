<?php
require_once dirname(__DIR__) . '/config.php';
Auth::requirePermission('view_payments');

$pageTitle = 'Daily report';
$activeMenu = 'daily';

$date = $_GET['date'] ?? date('Y-m-d');
$rows = Database::all(
    "SELECT p.*, c.name AS class_name FROM payment_transactions p
     LEFT JOIN school_classes c ON c.id = p.class_id
     WHERE p.status = 'approved' AND DATE(p.approved_at) = ?
     ORDER BY p.approved_at DESC",
    [$date]
);
$byChannel = [];
foreach ($rows as $row) {
    $byChannel[$row['channel']] = ($byChannel[$row['channel']] ?? 0) + (float) $row['amount'];
}

require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Collections for <?= pretty_date($date) ?></h2>
        <form method="get" class="flex gap-1">
            <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()">
            <button class="btn btn-ghost btn-sm" type="button" onclick="window.print()">Print</button>
        </form>
    </div>

    <div class="stats">
        <div class="stat green"><span class="ico">💵</span><div><strong><?= money(array_sum(array_column($rows, 'amount'))) ?></strong><small>Total collected</small></div></div>
        <div class="stat purple"><span class="ico">🧾</span><div><strong><?= count($rows) ?></strong><small>Receipts issued</small></div></div>
        <?php foreach ($byChannel as $channel => $amount): ?>
            <div class="stat blue"><span class="ico">🏦</span><div><strong><?= money($amount) ?></strong><small><?= e(ucfirst(str_replace('-', ' ', $channel))) ?></small></div></div>
        <?php endforeach; ?>
    </div>

    <div class="table-wrap mt-2">
        <table class="data">
            <thead><tr><th>Time</th><th>Receipt</th><th>Student</th><th>Class</th><th>Channel</th><th>Amount</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= pretty_date($row['approved_at'], 'H:i') ?></td>
                    <td><?= e($row['receipt_number']) ?></td>
                    <td><?= e($row['student_name']) ?></td>
                    <td><?= e($row['class_name'] ?? '—') ?></td>
                    <td><?= e(ucfirst(str_replace('-', ' ', $row['channel']))) ?></td>
                    <td><?= money($row['amount']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="6" class="muted">No payments were approved on this date.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
