<?php
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('teacher');

$pageTitle = 'My timetable';
$activeMenu = 'timetable';

$sessionName = current_session_name();
$term = current_term();
$slots = TimetableManager::forTeacher((int) $user['id'], $sessionName, $term);
$grid = [];
foreach ($slots as $slot) {
    $grid[$slot['day_of_week']][(int) $slot['period']] = $slot;
}

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Weekly timetable</h2><span class="muted"><?= e($sessionName) ?> · <?= e($term) ?> Term</span></div>
    <div class="table-wrap">
        <table class="timetable">
            <thead><tr><th>Period</th><?php foreach (TimetableManager::DAYS as $day): ?><th><?= e($day) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach (TimetableManager::PERIODS as $period => $time): ?>
                <tr>
                    <th><?= (int) $period ?><br><small><?= e($time['starts_at']) ?>–<?= e($time['ends_at']) ?></small></th>
                    <?php foreach (TimetableManager::DAYS as $day): $slot = $grid[$day][$period] ?? null; ?>
                        <td class="slot <?= $slot ? 'filled' : '' ?>">
                            <?php if ($slot): ?>
                                <strong><?= e($slot['subject_name']) ?></strong><small><?= e($slot['class_name']) ?><?= $slot['room'] ? ' · ' . e($slot['room']) : '' ?></small>
                            <?php else: ?><span class="muted">free</span><?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
