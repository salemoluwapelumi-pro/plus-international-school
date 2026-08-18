<?php
/**
 * Result entry: CA1 (10), CA2 (10), assignment (10) and exam (70). Totals,
 * grades and remarks are calculated instantly in the browser and again on the
 * server when the sheet is saved.
 */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_results');

$pageTitle = 'Upload results';
$pageSubtitle = 'CA 10 + CA 10 + assignment 10 + exam 70 = 100';
$activeMenu = 'upload-results';

$classes = classes_list();
$subjects = Database::all('SELECT * FROM subjects ORDER BY name');
$classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
$subjectId = (int) ($_GET['subject_id'] ?? ($subjects[0]['id'] ?? 0));
$term = $_GET['term'] ?? current_term();
$sessionName = current_session_name();

$students = $classId
    ? Database::all("SELECT id, full_name, admission_no FROM users WHERE class_id = ? AND role = 'student' ORDER BY full_name", [$classId])
    : [];

$existing = [];
if ($classId && $subjectId) {
    foreach (Database::all(
        'SELECT * FROM results WHERE class_id = ? AND subject_id = ? AND session_name = ? AND term = ?',
        [$classId, $subjectId, $sessionName, $term]
    ) as $row) {
        $existing[(int) $row['student_id']] = $row;
    }
}

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Choose the class, subject and term</h2><span class="muted"><?= e($sessionName) ?></span></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id">
                <?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id">
                <?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>" <?= $subjectId === (int) $subject['id'] ? 'selected' : '' ?>><?= e($subject['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="term">Term</label>
            <select id="term" name="term">
                <?php foreach (['First', 'Second', 'Third'] as $option): ?><option value="<?= $option ?>" <?= $term === $option ? 'selected' : '' ?>><?= $option ?> Term</option><?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Load score sheet</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Score sheet (<?= count($students) ?> students)</h2><span class="muted">Totals and grades update as you type</span></div>
    <form method="post" action="<?= url('backend/api/results/save.php') ?>">
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
        <input type="hidden" name="term" value="<?= e($term) ?>">
        <input type="hidden" name="session_name" value="<?= e($sessionName) ?>">
        <div class="table-wrap">
            <table class="data" id="resultEntryTable">
                <thead><tr><th>Student</th><th>Admission no.</th><th>CA 1 (10)</th><th>CA 2 (10)</th><th>Assignment (10)</th><th>Exam (70)</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead>
                <tbody>
                <?php foreach ($students as $student): $row = $existing[(int) $student['id']] ?? null; ?>
                    <tr>
                        <td><?= e($student['full_name']) ?><input type="hidden" name="student_id[]" value="<?= (int) $student['id'] ?>"></td>
                        <td><?= e($student['admission_no'] ?? '—') ?></td>
                        <td><input type="number" min="0" max="10" step="0.5" name="ca1[]" value="<?= $row ? (float) $row['ca1'] : 0 ?>"></td>
                        <td><input type="number" min="0" max="10" step="0.5" name="ca2[]" value="<?= $row ? (float) $row['ca2'] : 0 ?>"></td>
                        <td><input type="number" min="0" max="10" step="0.5" name="assignment[]" value="<?= $row ? (float) $row['assignment'] : 0 ?>"></td>
                        <td><input type="number" min="0" max="70" step="0.5" name="exam[]" value="<?= $row ? (float) $row['exam'] : 0 ?>"></td>
                        <td class="cell-total"><?= $row ? (float) $row['total'] : '0' ?></td>
                        <td class="cell-grade badge"><?= $row ? e($row['grade']) : '—' ?></td>
                        <td class="cell-remark"><?= $row ? e($row['remark']) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$students): ?><tr><td colspan="9" class="muted">No students in this class yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="stats mt-2" id="resultSummary">
            <div class="stat purple"><div><strong data-subjects>0</strong><small>Students on the sheet</small></div></div>
            <div class="stat gold"><div><strong data-total>0</strong><small>Sum of scores</small></div></div>
            <div class="stat green"><div><strong data-average>0</strong><small>Class average</small></div></div>
            <div class="stat blue"><div><strong data-grade>—</strong><small>Average grade</small></div></div>
        </div>
        <?php if ($students): ?>
            <button class="btn btn-primary mt-2" type="submit">Save scores &amp; compute results</button>
        <?php endif; ?>
    </form>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
