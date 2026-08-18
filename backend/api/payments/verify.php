<?php
/** Marks a payment as checked by the cashier before final approval. */
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::requirePermission('approve_payments');
$paymentId = (int) (request_input()['payment_id'] ?? 0);
if (!$paymentId) {
    json_response(['ok' => false, 'error' => 'Select a payment to verify.'], 422);
}

PaymentProcessor::verify($paymentId, (int) $user['id']);
flash('Payment marked as verified.');
redirect('/cashier/verify-payments.php');
