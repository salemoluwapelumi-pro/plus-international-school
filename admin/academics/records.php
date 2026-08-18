<?php
/** Permanent student records — documents and history kept from inception to date. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_records');

$pageTitle = 'Student records';
$pageSubtitle = 'Permanent records and documents for every student, past and present';
$activeMenu = 'records';

$students = Database::all("SELECT u.id, u.full_name, u.admission_no, c.name AS class_name
                           FROM users u LEFT JOIN school_classes c ON c.id = u.class_id
                           WHERE u.role = 'student' ORDER BY u.full_name");
$studentId = (int) ($_GET['student_id'] ?? ($students[0]['id'] ?? 0));
$records = $studentId ? Database::all('SELECT * FROM student_records WHERE student_id = ? ORDER BY id DESC', [$studentId]) : [];

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Select a student</h2></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" onchange="this.form.submit()">
                <?php foreach ($students as $student): ?>
                    <option value="<?= (int) $student['id'] ?>" <?= $studentId === (int) $student['id'] ? 'selected' : '' ?>>
                        <?= e($student['full_name']) ?> — <?= e($student['admission_no'] ?: 'no admission no.') ?> (<?= e($student['class_name'] ?? 'unassigned') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Open records</button>
    </form>
</div>

<?php if ($studentId): ?>
<div class="panel mt-3">
    <div class="panel-head"><h2>Add a record</h2></div>
    <form method="post" action="<?= url('backend/api/records/save.php') ?>" enctype="multipart/form-data">
        <input type="hidden" name="student_id" value="<?= $studentId ?>">
        <div class="form-grid">
            <div class="field">
                <label for="record_type">Record type</label>
                <select id="record_type" name="record_type">
                    <option value="admission">Admission document</option>
                    <option value="birth_certificate">Birth certificate</option>
                    <option value="report_card">Report card</option>
                    <option value="medical">Medical</option>
                    <option value="transfer">Transfer certificate</option>
                    <option value="testimonial">Testimonial</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="field"><label for="title">Title</label><input id="title" name="title" required></div>
            <div class="field"><label for="session_name">Session</label><input id="session_name" name="session_name" value="<?= e(current_session_name()) ?>"></div>
            <div class="field"><label for="document">Document (pdf, image, doc)</label><input id="document" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"></div>
            <div class="field field-full"><label for="description">Notes</label><textarea id="description" name="description" rows="3"></textarea></div>
        </div>
        <button class="btn btn-primary" type="submit">Save record</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Records on file (<?= count($records) ?>)</h2></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Session</th><th>Document</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= pretty_date($record['created_at']) ?></td>
                    <td><?= e(ucfirst(str_replace('_', ' ', $record['record_type']))) ?></td>
                    <td><?= e($record['title']) ?><?= $record['description'] ? '<br><small class="muted">' . e($record['description']) . '</small>' : '' ?></td>
                    <td><?= e($record['session_name']) ?></td>
                    <td><?= $record['file_path'] ? '<a target="_blank" href="' . e(url($record['file_path'])) . '">Open</a>' : '—' ?></td>
                    <td class="actions">
                        <form method="post" action="<?= url('backend/api/records/delete.php') ?>" data-confirm="Delete this record permanently?">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="6" class="muted">No records on file for this student yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
