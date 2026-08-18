<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('teacher');

$pageTitle = 'Assignments';
$activeMenu = 'assignments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim((string) $_POST['title']) !== '') {
        Database::insertRow('assignments', [
            'class_id'    => (int) $_POST['class_id'],
            'subject_id'  => (int) $_POST['subject_id'],
            'teacher_id'  => (int) $user['id'],
            'title'       => trim((string) $_POST['title']),
            'description' => $_POST['description'] ?: null,
            'due_date'    => $_POST['due_date'] ?: null,
        ]);
        $students = Database::all("SELECT id FROM users WHERE class_id = ? AND role = 'student'", [(int) $_POST['class_id']]);
        foreach ($students as $student) {
            NotificationSystem::send((int) $student['id'], 'New assignment: ' . $_POST['title'], 'Due ' . pretty_date($_POST['due_date'] ?: null));
        }
        flash('Assignment published to the class.');
    }
    redirect('/teacher/academics/assignments.php');
}

$classes = classes_list();
$subjects = Database::all('SELECT * FROM subjects ORDER BY name');
$assignments = Database::all(
    'SELECT a.*, c.name AS class_name, s.name AS subject_name FROM assignments a
     JOIN school_classes c ON c.id = a.class_id
     JOIN subjects s ON s.id = a.subject_id
     WHERE a.teacher_id = ? ORDER BY a.id DESC LIMIT 50',
    [$user['id']]
);

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Set an assignment</h2></div>
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" required>
                    <?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= e($class['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>"><?= e($subject['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="title">Title</label><input id="title" name="title" required></div>
            <div class="field"><label for="due_date">Due date</label><input id="due_date" type="date" name="due_date"></div>
            <div class="field field-full"><label for="description">Instructions</label><textarea id="description" name="description" rows="4"></textarea></div>
        </div>
        <button class="btn btn-primary" type="submit">Publish assignment</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>My assignments</h2></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Set</th><th>Class</th><th>Subject</th><th>Title</th><th>Due</th></tr></thead>
            <tbody>
            <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td><?= pretty_date($assignment['created_at']) ?></td>
                    <td><?= e($assignment['class_name']) ?></td>
                    <td><?= e($assignment['subject_name']) ?></td>
                    <td><?= e($assignment['title']) ?></td>
                    <td><?= pretty_date($assignment['due_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$assignments): ?><tr><td colspan="5" class="muted">You have not set any assignments yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
