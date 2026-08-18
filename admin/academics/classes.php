<?php
/** Classes, subjects and the academic session currently in progress. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requireRole('superadmin', 'subadmin');

$pageTitle = 'Classes & subjects';
$activeMenu = 'classes';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::is('superadmin')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'class' && trim((string) $_POST['name']) !== '') {
        Database::insertRow('school_classes', [
            'name'        => trim((string) $_POST['name']),
            'section'     => $_POST['section'] ?: 'primary',
            'level_order' => (int) ($_POST['level_order'] ?? 0),
        ]);
        flash('Class added.');
    }
    if ($action === 'subject' && trim((string) $_POST['name']) !== '') {
        Database::insertRow('subjects', [
            'name'    => trim((string) $_POST['name']),
            'code'    => $_POST['code'] ?: null,
            'section' => $_POST['section'] ?: 'primary',
        ]);
        flash('Subject added.');
    }
    if ($action === 'session' && trim((string) $_POST['name']) !== '') {
        Database::run('UPDATE academic_sessions SET is_current = 0');
        Database::insertRow('academic_sessions', [
            'name'       => trim((string) $_POST['name']),
            'term'       => $_POST['term'] ?: 'First',
            'starts_on'  => $_POST['starts_on'] ?: null,
            'ends_on'    => $_POST['ends_on'] ?: null,
            'is_current' => 1,
        ]);
        flash('Academic session updated.');
    }
    redirect('/admin/academics/classes.php');
}

$classes = classes_list();
$subjects = Database::all('SELECT * FROM subjects ORDER BY section, name');
$sessions = Database::all('SELECT * FROM academic_sessions ORDER BY id DESC LIMIT 10');

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="grid grid-2">
    <div class="panel">
        <div class="panel-head"><h2>Classes (<?= count($classes) ?>)</h2></div>
        <?php if (Auth::is('superadmin')): ?>
        <form method="post" class="filters mb-2">
            <input type="hidden" name="action" value="class">
            <div class="field"><label for="class-name">Class name</label><input id="class-name" name="name" required></div>
            <div class="field">
                <label for="class-section">Section</label>
                <select id="class-section" name="section"><option value="nursery">Nursery</option><option value="primary">Primary</option><option value="secondary">Secondary</option></select>
            </div>
            <div class="field"><label for="class-order">Order</label><input id="class-order" type="number" name="level_order" value="<?= count($classes) + 1 ?>"></div>
            <button class="btn btn-primary" type="submit">Add class</button>
        </form>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>#</th><th>Class</th><th>Section</th><th>Students</th></tr></thead>
                <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?= (int) $class['level_order'] ?></td>
                        <td><?= e($class['name']) ?></td>
                        <td><?= e(ucfirst($class['section'])) ?></td>
                        <td><?= (int) Database::value("SELECT COUNT(*) FROM users WHERE class_id = ? AND role = 'student'", [$class['id']]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Subjects (<?= count($subjects) ?>)</h2></div>
        <?php if (Auth::is('superadmin')): ?>
        <form method="post" class="filters mb-2">
            <input type="hidden" name="action" value="subject">
            <div class="field"><label for="subject-name">Subject</label><input id="subject-name" name="name" required></div>
            <div class="field"><label for="subject-code">Code</label><input id="subject-code" name="code"></div>
            <div class="field">
                <label for="subject-section">Section</label>
                <select id="subject-section" name="section"><option value="nursery">Nursery</option><option value="primary">Primary</option><option value="secondary">Secondary</option></select>
            </div>
            <button class="btn btn-primary" type="submit">Add subject</button>
        </form>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Subject</th><th>Code</th><th>Section</th></tr></thead>
                <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <tr><td><?= e($subject['name']) ?></td><td><?= e($subject['code'] ?? '—') ?></td><td><?= e(ucfirst($subject['section'])) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Academic session</h2><span class="badge badge-purple"><?= e(current_session_name()) ?> · <?= e(current_term()) ?> Term</span></div>
    <?php if (Auth::is('superadmin')): ?>
    <form method="post" class="filters">
        <input type="hidden" name="action" value="session">
        <div class="field"><label for="session-name">Session</label><input id="session-name" name="name" placeholder="2025/2026" required></div>
        <div class="field">
            <label for="session-term">Term</label>
            <select id="session-term" name="term"><option>First</option><option>Second</option><option>Third</option></select>
        </div>
        <div class="field"><label for="session-start">Begins</label><input id="session-start" type="date" name="starts_on"></div>
        <div class="field"><label for="session-end">Ends</label><input id="session-end" type="date" name="ends_on"></div>
        <button class="btn btn-primary" type="submit">Set as current</button>
    </form>
    <?php endif; ?>
    <div class="table-wrap mt-2">
        <table class="data">
            <thead><tr><th>Session</th><th>Term</th><th>Begins</th><th>Ends</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($sessions as $row): ?>
                <tr>
                    <td><?= e($row['name']) ?></td><td><?= e($row['term']) ?></td>
                    <td><?= pretty_date($row['starts_on']) ?></td><td><?= pretty_date($row['ends_on']) ?></td>
                    <td><?= $row['is_current'] ? '<span class="badge badge-green">Current</span>' : '<span class="badge badge-gray">Closed</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
