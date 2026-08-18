<?php
require_once dirname(__DIR__) . '/config.php';
$user = Auth::requireRole('teacher');

$pageTitle = 'Teacher dashboard';
$pageSubtitle = 'Your classes, lessons and result entry';
$activeMenu = 'dashboard';

$sessionName = current_session_name();
$term = current_term();
$today = date('l');
$lessons = in_array($today, TimetableManager::DAYS, true)
    ? array_values(array_filter(TimetableManager::forTeacher((int) $user['id'], $sessionName, $term), static fn ($slot) => $slot['day_of_week'] === $today))
    : [];
$myClasses = Database::all(
    'SELECT DISTINCT c.id, c.name FROM timetable_slots t JOIN school_classes c ON c.id = t.class_id
     WHERE t.teacher_id = ? AND t.session_name = ? AND t.term = ? ORDER BY c.level_order',
    [$user['id'], $sessionName, $term]
);
$entered = (int) Database::value(
    'SELECT COUNT(*) FROM results WHERE entered_by = ? AND session_name = ? AND term = ?',
    [$user['id'], $sessionName, $term]
);

require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat purple"><span class="ico">🏫</span><div><strong><?= count($myClasses) ?></strong><small>Classes I teach</small></div></div>
    <div class="stat gold"><span class="ico">📅</span><div><strong><?= count($lessons) ?></strong><small>Lessons today</small></div></div>
    <div class="stat green"><span class="ico">📝</span><div><strong><?= $entered ?></strong><small>Scores entered this term</small></div></div>
    <div class="stat blue"><span class="ico">📖</span><div><strong><?= e($term) ?> Term</strong><small><?= e($sessionName) ?></small></div></div>
</div>

<div class="grid grid-2 mt-3">
    <div class="panel">
        <div class="panel-head"><h2>Today’s lessons — <?= e($today) ?></h2><a class="btn btn-ghost btn-sm" href="<?= url('teacher/profile/timetable.php') ?>">Full timetable</a></div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Period</th><th>Time</th><th>Class</th><th>Subject</th><th>Room</th></tr></thead>
                <tbody>
                <?php foreach ($lessons as $lesson): ?>
                    <tr>
                        <td><?= (int) $lesson['period'] ?></td>
                        <td><?= e(substr($lesson['starts_at'], 0, 5)) ?>–<?= e(substr($lesson['ends_at'], 0, 5)) ?></td>
                        <td><?= e($lesson['class_name']) ?></td>
                        <td><?= e($lesson['subject_name']) ?></td>
                        <td><?= e($lesson['room'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$lessons): ?><tr><td colspan="5" class="muted">No lessons scheduled for today.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Quick actions</h2></div>
        <div class="grid">
            <a class="btn btn-primary" href="<?= url('teacher/academics/upload-results.php') ?>">Upload results</a>
            <a class="btn btn-ghost" href="<?= url('teacher/academics/attendance.php') ?>">Take attendance</a>
            <a class="btn btn-ghost" href="<?= url('teacher/academics/assignments.php') ?>">Set an assignment</a>
            <a class="btn btn-gold" href="<?= url('chat/login.php') ?>">Open the chat system</a>
        </div>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
