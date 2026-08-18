<?php
/** Printable receipt for an approved payment. */
require_once dirname(__DIR__) . '/config.php';
Auth::requirePermission('view_payments');

$payment = Database::one(
    'SELECT p.*, c.name AS class_name, u.full_name AS cashier_name
     FROM payment_transactions p
     LEFT JOIN school_classes c ON c.id = p.class_id
     LEFT JOIN users u ON u.id = p.approver_id
     WHERE p.id = ?',
    [(int) ($_GET['id'] ?? 0)]
);

if (!$payment) {
    http_response_code(404);
    exit('Receipt not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receipt <?= e($payment['receipt_number'] ?: $payment['reference']) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
</head>
<body style="background:#f4f5fa;padding:24px">
<div style="max-width:720px;margin:0 auto">
    <div class="flex-between no-print mb-2">
        <a class="btn btn-ghost btn-sm" href="<?= url('cashier/receipts.php') ?>">← Back</a>
        <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">Print receipt</button>
    </div>

    <div class="result-sheet">
        <div class="result-head">
            <div class="crest">PIS</div>
            <div>
                <h2><?= e(SCHOOL_NAME) ?></h2>
                <p><?= e(SCHOOL_ADDRESS) ?> · <?= e(SCHOOL_PHONE) ?></p>
                <p><strong>Official fee receipt</strong></p>
            </div>
        </div>

        <div class="result-meta">
            <div><span>Receipt number</span><strong><?= e($payment['receipt_number'] ?: 'Pending approval') ?></strong></div>
            <div><span>Reference</span><strong><?= e($payment['reference']) ?></strong></div>
            <div><span>Date</span><strong><?= pretty_date($payment['approved_at'] ?: $payment['created_at']) ?></strong></div>
            <div><span>Status</span><strong><?= e(ucfirst($payment['status'])) ?></strong></div>
        </div>

        <table>
            <thead><tr><th style="text-align:left">Description</th><th>Term</th><th>Class</th><th>Amount</th></tr></thead>
            <tbody>
                <tr>
                    <td class="subject-name">School fees — <?= e($payment['student_name']) ?></td>
                    <td><?= e($payment['term']) ?></td>
                    <td><?= e($payment['class_name'] ?? '—') ?></td>
                    <td><strong><?= money($payment['amount']) ?></strong></td>
                </tr>
                <tr>
                    <td class="subject-name">Expected for the term</td><td colspan="2"></td><td><?= money($payment['amount_expected']) ?></td>
                </tr>
                <tr>
                    <td class="subject-name">Balance</td><td colspan="2"></td>
                    <td><?= money(max(0, (float) $payment['amount_expected'] - (float) $payment['amount'])) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="signature">
            <div>Cashier: <?= e($payment['cashier_name'] ?? '—') ?></div>
            <div>Bursary stamp</div>
        </div>
        <p class="text-center muted mt-2" style="font-size:.78rem">Paid by <?= e(ucfirst(str_replace('-', ' ', $payment['channel']))) ?>. This receipt is computer generated.</p>
    </div>
</div>
</body>
</html>
