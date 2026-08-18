<?php
/** Printable official result sheet for one student. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_results');

$studentId = (int) ($_GET['student_id'] ?? 0);
$sheetSession = $_GET['session_name'] ?? current_session_name();
$sheetTerm = $_GET['term'] ?? current_term();
$sheet = ResultCalculator::sheet($studentId, $sheetSession, $sheetTerm);

if (!$sheet['student']) {
    http_response_code(404);
    exit('Student not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Result sheet · <?= e($sheet['student']['full_name']) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
</head>
<body style="background:#f4f5fa;padding:24px">
<div style="max-width:900px;margin:0 auto">
    <div class="flex-between no-print mb-2">
        <a class="btn btn-ghost btn-sm" href="<?= url('admin/academics/results.php') ?>">← Back to results</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()" type="button">Print result sheet</button>
    </div>
    <?php require dirname(__DIR__, 2) . '/backend/includes/layout/result-sheet.php'; ?>
</div>
</body>
</html>
