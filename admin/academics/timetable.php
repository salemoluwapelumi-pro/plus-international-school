<?php
/** Weekly timetable builder: class by class, Monday to Friday, repeating every week. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_timetable');

$pageTitle = 'Timetable';
$pageSubtitle = 'Class, subject, period, day and teacher — repeats weekly through the term';
$activeMenu = 'timetable';

$classes = classes_list();
$classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
$subjects = Database::all('SELECT * FROM subjects ORDER BY name');
$teachers = Database::all("SELECT id, full_name FROM users WHERE role = 'teacher' ORDER BY full_name");
$sessionName = current_session_name();
$term = current_term();
$slots = $classId ? TimetableManager::forClass($classId, $sessionName, $term) : [];

$grid = [];
foreach ($slots as $slot) {
    $grid[$slot['day_of_week']][(int) $slot['period']] = $slot;
}

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Class timetable</h2><span class="muted"><?= e($sessionName) ?> · <?= e($term) ?> Term</span></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id" onchange="this.form.submit()">
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Show timetable</button>
    </form>

    <div class="table-wrap mt-2">
        <table class="timetable">
            <thead>
                <tr>
                    <th>Period</th>
                    <?php foreach (TimetableManager::DAYS as $day): ?><th><?= e($day) ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach (TimetableManager::PERIODS as $period => $time): ?>
                <tr>
                    <th><?= (int) $period ?><br><small><?= e($time['starts_at']) ?>–<?= e($time['ends_at']) ?></small></th>
                    <?php foreach (TimetableManager::DAYS as $day): $slot = $grid[$day][$period] ?? null; ?>
                        <td class="slot <?= $slot ? 'filled' : '' ?>">
                            <?php if ($slot): ?>
                                <strong><?= e($slot['subject_name']) ?></strong>
                                <small><?= e($slot['teacher_name'] ?? 'Unassigned') ?><?= $slot['room'] ? ' · ' . e($slot['room']) : '' ?></small>
                                <form method="post" action="<?= url('backend/api/timetable/delete.php') ?>" data-confirm="Remove this period?">
                                    <input type="hidden" name="id" value="<?= (int) $slot['id'] ?>">
                                    <input type="hidden" name="class_id" value="<?= $classId ?>">
                                    <button class="link-danger" type="submit">remove</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">free</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Add or replace a period</h2></div>
    <form method="post" action="<?= url('backend/api/timetable/save.php') ?>">
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        <div class="form-grid">
            <div class="field">
                <label for="day_of_week">Day</label>
                <select id="day_of_week" name="day_of_week" required>
                    <?php foreach (TimetableManager::DAYS as $day): ?><option value="<?= e($day) ?>"><?= e($day) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="period">Period</label>
                <select id="period" name="period" required>
                    <?php foreach (TimetableManager::PERIODS as $period => $time): ?>
                        <option value="<?= (int) $period ?>">Period <?= (int) $period ?> (<?= e($time['starts_at']) ?>–<?= e($time['ends_at']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>"><?= e($subject['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="">Unassigned</option>
                    <?php foreach ($teachers as $teacher): ?><option value="<?= (int) $teacher['id'] ?>"><?= e($teacher['full_name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="room">Room</label><input id="room" name="room" placeholder="e.g. Block B / Lab 2"></div>
        </div>
        <button class="btn btn-primary" type="submit">Save period</button>
    </form>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
