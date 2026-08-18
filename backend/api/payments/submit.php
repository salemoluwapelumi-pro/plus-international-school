<?php
/** Records a fee payment; it lands in the cashier's approval queue immediately. */
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::user();
$input = request_input();

$studentId = (int) ($input['student_id'] ?? 0);
if (!$studentId && $user && $user['role'] === 'student') {
    $studentId = (int) $user['id'];
}
if (!$studentId && !empty($input['admission_no'])) {
    $studentId = (int) (Database::value("SELECT id FROM users WHERE admission_no = ? AND role = 'student'", [$input['admission_no']]) ?? 0);
}

if (!$studentId) {
    json_response(['ok' => false, 'error' => 'We could not identify the student. Sign in or enter a valid admission number.'], 422);
}

$student = Database::one('SELECT * FROM users WHERE id = ?', [$studentId]);
if (!$student) {
    json_response(['ok' => false, 'error' => 'Student not found.'], 404);
}

$classId = (int) ($input['class_id'] ?? $student['class_id']);
$term = (string) ($input['term'] ?? current_term());
$amount = round((float) ($input['amount'] ?? 0), 2);
$channel = in_array($input['channel'] ?? '', ['paystack', 'remita', 'bank-transfer', 'cash'], true) ? $input['channel'] : 'bank-transfer';

if ($amount <= 0) {
    json_response(['ok' => false, 'error' => 'Enter the amount paid.'], 422);
}
if (!$classId) {
    json_response(['ok' => false, 'error' => 'Select the student class.'], 422);
}

// A Paystack payment is confirmed with the gateway before it is recorded.
if ($channel === 'paystack' && !empty($input['gateway_ref']) && PAYSTACK_SECRET_KEY !== '') {
    $verification = PaymentProcessor::verifyPaystack((string) $input['gateway_ref']);
    if (empty($verification['status']) || ($verification['data']['status'] ?? '') !== 'success') {
        json_response(['ok' => false, 'error' => 'Paystack could not confirm this transaction.'], 402);
    }
    $amount = round(((float) $verification['data']['amount']) / 100, 2);
}

$result = PaymentProcessor::submit([
    'student_id'     => $studentId,
    'payer_id'       => $user['id'] ?? null,
    'student_name'   => $input['student_name'] ?? $student['full_name'],
    'class_id'       => $classId,
    'term'           => $term,
    'session_name'   => current_session_name(),
    'amount'         => $amount,
    'channel'        => $channel,
    'gateway_ref'    => $input['gateway_ref'] ?? null,
    'student_status' => in_array($input['student_status'] ?? '', ['new', 'returning'], true) ? $input['student_status'] : $student['student_status'],
    'proof_path'     => store_upload('proof', 'receipts'),
    'status'         => 'submitted',
]);

json_response([
    'ok' => true,
    'reference' => $result['reference'],
    'message' => 'Payment recorded and sent to the cashier for approval.',
]);
