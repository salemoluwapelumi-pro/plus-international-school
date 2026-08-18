<?php
/** A student sees a result only after the school has published it. */
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('student');

$pageTitle = 'My results';
$activeMenu = 'results';

$sheetSession = $_GET['session_name'] ?? current_session_name();
$sheetTerm = $_GET['term'] ?? current_term();
$sheet = ResultCalculator::sheet((int) $user['id'], $sheetSession, $sheetTerm);
$published = $sheet['summary'] && $sheet['summary']['published'];
$sessions = Database::all('SELECT DISTINCT session_name FROM results WHERE student_id = ? ORDER BY session_name DESC', [$user['id']]);

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel no-print">
    <div class="panel-head"><h2>Choose a term</h2></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="session_name">Session</label>
            <select id="session_name" name="session_name">
                <?php $options = array_column($sessions, 'session_name') ?: [current_session_name()]; ?>
                <?php foreach ($options as $option): ?><option value="<?= e($option) ?>" <?= $option === $sheetSession ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?>
            </select>
        </div>
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
    <div class="panel mt-3"><div class="empty-state"><div class="ico">⏳</div><h3>Result not published</h3><p>Your <?= e($sheetTerm) ?> term result for <?= e($sheetSession) ?> has not been published yet. You will be notified as soon as it is.</p></div></div>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
