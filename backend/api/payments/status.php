<?php
require_once dirname(__DIR__, 3) . '/config.php';

Auth::requirePermission('view_payments');
json_response(['ok' => true] + PaymentProcessor::totals());
