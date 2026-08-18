<?php
/** Cashier approval — issues the receipt number. No admin approval is required. */
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::requirePermission('approve_payments');
$input = request_input();
$paymentId = (int) ($input['payment_id'] ?? 0);
if (!$paymentId) {
    json_response(['ok' => false, 'error' => 'Select a payment to approve.'], 422);
}

$receipt = PaymentProcessor::approve($paymentId, (int) $user['id']);
$payment = Database::one('SELECT * FROM payment_transactions WHERE id = ?', [$paymentId]);
if ($payment && $payment['student_id']) {
    NotificationSystem::send(
        (int) $payment['student_id'],
        'Fee payment approved',
        'Your payment of ' . money($payment['amount']) . ' has been approved. Receipt ' . $receipt . '.'
    );
}

if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
    json_response(['ok' => true, 'receipt_number' => $receipt]);
}
flash('Payment approved. Receipt ' . $receipt . ' issued.');
redirect('/cashier/verify-payments.php');
