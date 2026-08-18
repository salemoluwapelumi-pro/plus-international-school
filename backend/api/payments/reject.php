<?php
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::requirePermission('approve_payments');
$input = request_input();
$paymentId = (int) ($input['payment_id'] ?? 0);
$note = trim((string) ($input['note'] ?? 'No reason given'));
if (!$paymentId) {
    json_response(['ok' => false, 'error' => 'Select a payment to reject.'], 422);
}

PaymentProcessor::reject($paymentId, (int) $user['id'], $note);
$payment = Database::one('SELECT * FROM payment_transactions WHERE id = ?', [$paymentId]);
if ($payment && $payment['student_id']) {
    NotificationSystem::send((int) $payment['student_id'], 'Fee payment rejected', $note);
}

flash('Payment rejected.', 'warning');
redirect('/cashier/verify-payments.php');
