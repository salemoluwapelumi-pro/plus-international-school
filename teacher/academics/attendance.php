<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('teacher');

$pageTitle = 'Attendance';
$activeMenu = 'attendance';

$classes = classes_list();
$classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
$date = $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = (int) $_POST['class_id'];
    $date = $_POST['date'];
    foreach ((array) ($_POST['status'] ?? []) as $studentId => $status) {
        Database::run(
            'INSERT INTO attendance (student_id, class_id, attendance_date, status, marked_by)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)',
            [(int) $studentId, $classId, $date, in_array($status, ['present', 'absent', 'late'], true) ? $status : 'present', $user['id']]
        );
    }
    flash('Attendance saved for ' . pretty_date($date) . '.');
    redirect('/teacher/academics/attendance.php?class_id=' . $classId . '&date=' . $date);
}

$students = Database::all("SELECT id, full_name, admission_no FROM users WHERE class_id = ? AND role = 'student' ORDER BY full_name", [$classId]);
$marked = [];
foreach (Database::all('SELECT * FROM attendance WHERE class_id = ? AND attendance_date = ?', [$classId, $date]) as $row) {
    $marked[(int) $row['student_id']] = $row['status'];
}

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Mark attendance</h2></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id">
                <?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label for="date">Date</label><input id="date" type="date" name="date" value="<?= e($date) ?>"></div>
        <button class="btn btn-primary" type="submit">Load register</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Register — <?= pretty_date($date) ?></h2></div>
    <form method="post">
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        <input type="hidden" name="date" value="<?= e($date) ?>">
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Student</th><th>Admission no.</th><th>Present</th><th>Absent</th><th>Late</th></tr></thead>
                <tbody>
                <?php foreach ($students as $student): $status = $marked[(int) $student['id']] ?? 'present'; ?>
                    <tr>
                        <td><?= e($student['full_name']) ?></td>
                        <td><?= e($student['admission_no'] ?? '—') ?></td>
                        <?php foreach (['present', 'absent', 'late'] as $option): ?>
                            <td><input type="radio" name="status[<?= (int) $student['id'] ?>]" value="<?= $option ?>" <?= $status === $option ? 'checked' : '' ?>></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$students): ?><tr><td colspan="5" class="muted">No students in this class.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($students): ?><button class="btn btn-primary mt-2" type="submit">Save attendance</button><?php endif; ?>
    </form>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
