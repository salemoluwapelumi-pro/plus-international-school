<?php
/**
 * Generates a Remita Retrieval Reference (RRR) so parents can pay at any bank
 * or on remita.net. In demo mode a local reference is issued instead of
 * calling the Remita API, so the flow works before live keys are configured.
 */
require_once dirname(__DIR__, 3) . '/config.php';

$input = request_input();
$amount = round((float) ($input['amount'] ?? 0), 2);
if ($amount <= 0) {
    json_response(['ok' => false, 'error' => 'Enter the amount to pay.'], 422);
}

if (REMITA_DEMO_MODE || REMITA_MERCHANT_ID === '') {
    json_response([
        'ok' => true,
        'rrr' => date('ymd') . random_int(100000, 999999),
        'demo' => true,
        'message' => 'Demo RRR generated. Configure REMITA_MERCHANT_ID and REMITA_API_KEY for live invoices.',
    ]);
}

$orderId = PaymentProcessor::reference();
$hash = hash('sha512', REMITA_MERCHANT_ID . REMITA_SERVICE_TYPE_ID . $orderId . $amount . url('/backend/api/payments/submit.php') . REMITA_API_KEY);

$payload = [
    'serviceTypeId' => REMITA_SERVICE_TYPE_ID,
    'amount'        => $amount,
    'orderId'       => $orderId,
    'payerName'     => $input['student_name'] ?? 'Parent',
    'payerEmail'    => $input['email'] ?? SCHOOL_EMAIL,
    'payerPhone'    => $input['phone'] ?? '',
    'description'   => 'School fees — ' . ($input['term'] ?? current_term()) . ' term',
];

$ch = curl_init('https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: remitaConsumerKey=' . REMITA_MERCHANT_ID . ',remitaConsumerToken=' . $hash,
    ],
    CURLOPT_TIMEOUT => 25,
]);
$response = curl_exec($ch);
curl_close($ch);

if (preg_match('/"RRR"\s*:\s*"?(\d+)"?/', (string) $response, $matches)) {
    json_response(['ok' => true, 'rrr' => $matches[1], 'order_id' => $orderId]);
}
json_response(['ok' => false, 'error' => 'Remita did not return an RRR. Please try another payment method.'], 502);
