<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('student');

$pageTitle = 'Assignments';
$activeMenu = 'assignments';

$assignments = $user['class_id'] ? Database::all(
    'SELECT a.*, s.name AS subject_name, t.full_name AS teacher_name
     FROM assignments a
     JOIN subjects s ON s.id = a.subject_id
     LEFT JOIN users t ON t.id = a.teacher_id
     WHERE a.class_id = ? ORDER BY a.id DESC LIMIT 100',
    [$user['class_id']]
) : [];

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Assignments for my class</h2></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Set</th><th>Subject</th><th>Title</th><th>Instructions</th><th>Teacher</th><th>Due</th></tr></thead>
            <tbody>
            <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td><?= pretty_date($assignment['created_at']) ?></td>
                    <td><?= e($assignment['subject_name']) ?></td>
                    <td><?= e($assignment['title']) ?></td>
                    <td class="muted"><?= e($assignment['description'] ?? '—') ?></td>
                    <td><?= e($assignment['teacher_name'] ?? '—') ?></td>
                    <td><?= pretty_date($assignment['due_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$assignments): ?><tr><td colspan="6" class="muted">No assignments have been set for your class yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
