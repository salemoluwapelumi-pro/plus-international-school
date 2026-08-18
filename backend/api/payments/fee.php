<?php
require_once dirname(__DIR__, 3) . '/config.php';

$classId = (int) ($_GET['class_id'] ?? 0);
$term = (string) ($_GET['term'] ?? current_term());
if (!$classId) {
    json_response(['ok' => false, 'error' => 'Select a class.'], 422);
}

json_response([
    'ok' => true,
    'amount' => PaymentProcessor::expectedFee($classId, $term, current_session_name()),
    'session_name' => current_session_name(),
]);
