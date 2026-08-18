<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('parent');

$pageTitle = 'Child result';
$activeMenu = 'child-results';

$studentId = (int) ($_GET['student_id'] ?? 0);
$linked = (int) Database::value('SELECT COUNT(*) FROM parent_students WHERE parent_id = ? AND student_id = ?', [$user['id'], $studentId]);
if (!$linked) {
    flash('That student is not linked to your account.', 'error');
    redirect('/parent/dashboard.php');
}

$sheetSession = $_GET['session_name'] ?? current_session_name();
$sheetTerm = $_GET['term'] ?? current_term();
$sheet = ResultCalculator::sheet($studentId, $sheetSession, $sheetTerm);
$published = $sheet['summary'] && $sheet['summary']['published'];

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel no-print">
    <div class="panel-head"><h2><?= e($sheet['student']['full_name'] ?? 'Student') ?></h2></div>
    <form method="get" class="filters">
        <input type="hidden" name="student_id" value="<?= $studentId ?>">
        <div class="field"><label for="session_name">Session</label><input id="session_name" name="session_name" value="<?= e($sheetSession) ?>"></div>
        <div class="field">
            <label for="term">Term</label>
            <select id="term" name="term">
                <?php foreach (['First', 'Second', 'Third'] as $option): ?><option value="<?= $option ?>" <?= $option === $sheetTerm ? 'selected' : '' ?>><?= $option ?> Term</option><?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">View result</button>
    </form>
</div>

<?php if ($published): ?>
    <div class="mt-3">
        <?php require dirname(__DIR__, 2) . '/backend/includes/layout/result-sheet.php'; ?>
        <div class="text-center mt-2 no-print"><button class="btn btn-primary" id="printResult" type="button">Print result sheet</button></div>
    </div>
<?php else: ?>
    <div class="panel mt-3"><div class="empty-state"><div class="ico">⏳</div><h3>Result not published</h3><p>This result has not been published yet.</p></div></div>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
