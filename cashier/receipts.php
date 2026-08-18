<?php
require_once dirname(__DIR__) . '/config.php';
Auth::requirePermission('view_payments');

$pageTitle = 'Receipts';
$pageSubtitle = 'Every receipt issued after approval';
$activeMenu = 'receipts';

$receipts = Database::all(
    "SELECT p.*, c.name AS class_name, u.full_name AS cashier_name
     FROM payment_transactions p
     LEFT JOIN school_classes c ON c.id = p.class_id
     LEFT JOIN users u ON u.id = p.approver_id
     WHERE p.status = 'approved' ORDER BY p.approved_at DESC LIMIT 300"
);
require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Issued receipts (<?= count($receipts) ?>)</h2><input class="table-search" data-table-search="#receiptTable" placeholder="Search receipts"></div>
    <div class="table-wrap">
        <table class="data" id="receiptTable">
            <thead><tr><th>Receipt</th><th>Date</th><th>Student</th><th>Class</th><th>Term</th><th>Amount</th><th>Approved by</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($receipts as $receipt): ?>
                <tr>
                    <td><strong><?= e($receipt['receipt_number']) ?></strong></td>
                    <td><?= pretty_date($receipt['approved_at']) ?></td>
                    <td><?= e($receipt['student_name']) ?></td>
                    <td><?= e($receipt['class_name'] ?? '—') ?></td>
                    <td><?= e($receipt['term']) ?></td>
                    <td><?= money($receipt['amount']) ?></td>
                    <td><?= e($receipt['cashier_name'] ?? '—') ?></td>
                    <td><a class="btn btn-ghost btn-sm" target="_blank" href="<?= url('cashier/receipt.php?id=' . (int) $receipt['id']) ?>">Print</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$receipts): ?><tr><td colspan="8" class="muted">No receipts issued yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
