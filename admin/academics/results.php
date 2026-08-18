<?php
/** Admin view of computed results, with publication control per class and term. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_results');

$pageTitle = 'Results';
$pageSubtitle = 'Compute, review and publish termly results';
$activeMenu = 'results';

$classes = classes_list();
$classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
$term = $_GET['term'] ?? current_term();
$sessionName = $_GET['session_name'] ?? current_session_name();

$summaries = $classId ? Database::all(
    'SELECT s.*, u.full_name, u.admission_no FROM result_summaries s
     JOIN users u ON u.id = s.student_id
     WHERE s.class_id = ? AND s.session_name = ? AND s.term = ?
     ORDER BY s.position',
    [$classId, $sessionName, $term]
) : [];

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Choose a class and term</h2></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id">
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="term">Term</label>
            <select id="term" name="term">
                <?php foreach (['First', 'Second', 'Third'] as $option): ?>
                    <option value="<?= $option ?>" <?= $term === $option ? 'selected' : '' ?>><?= $option ?> Term</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label for="session_name">Session</label><input id="session_name" name="session_name" value="<?= e($sessionName) ?>"></div>
        <button class="btn btn-primary" type="submit">Show results</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head">
        <h2>Class result summary</h2>
        <div class="flex gap-1">
            <form method="post" action="<?= url('backend/api/results/publish.php') ?>">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <input type="hidden" name="term" value="<?= e($term) ?>">
                <input type="hidden" name="session_name" value="<?= e($sessionName) ?>">
                <input type="hidden" name="publish" value="1">
                <button class="btn btn-primary btn-sm" type="submit">Compute &amp; publish</button>
            </form>
            <form method="post" action="<?= url('backend/api/results/publish.php') ?>">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <input type="hidden" name="term" value="<?= e($term) ?>">
                <input type="hidden" name="session_name" value="<?= e($sessionName) ?>">
                <input type="hidden" name="publish" value="0">
                <button class="btn btn-ghost btn-sm" type="submit">Unpublish</button>
            </form>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Position</th><th>Student</th><th>Admission no.</th><th>Subjects</th><th>Total</th><th>Average</th><th>Grade</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($summaries as $row): ?>
                <tr>
                    <td><?= ResultCalculator::ordinal($row['position'] ? (int) $row['position'] : null) ?></td>
                    <td><?= e($row['full_name']) ?></td>
                    <td><?= e($row['admission_no'] ?? '—') ?></td>
                    <td><?= (int) $row['subjects_count'] ?></td>
                    <td><?= (float) $row['total_score'] ?></td>
                    <td><?= (float) $row['average'] ?>%</td>
                    <td><span class="badge badge-purple"><?= e($row['overall_grade']) ?></span></td>
                    <td><span class="badge badge-<?= $row['published'] ? 'green' : 'gold' ?>"><?= $row['published'] ? 'Published' : 'Draft' ?></span></td>
                    <td><a class="btn btn-ghost btn-sm" target="_blank" href="<?= url('admin/academics/result-sheet.php?student_id=' . (int) $row['student_id'] . '&term=' . urlencode($term) . '&session_name=' . urlencode($sessionName)) ?>">Result sheet</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$summaries): ?>
                <tr><td colspan="9" class="muted">No results computed for this class and term yet. Teachers upload scores, then use “Compute &amp; publish”.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
