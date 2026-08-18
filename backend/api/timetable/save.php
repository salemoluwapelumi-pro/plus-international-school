<?php
require_once dirname(__DIR__, 3) . '/config.php';

Auth::requirePermission('manage_timetable');
$input = request_input();

$classId = (int) ($input['class_id'] ?? 0);
$day = (string) ($input['day_of_week'] ?? '');
$period = (int) ($input['period'] ?? 0);

if (!$classId || !in_array($day, TimetableManager::DAYS, true) || !$period) {
    flash('Choose a class, day and period.', 'error');
    redirect('/admin/academics/timetable.php');
}

TimetableManager::save([
    'class_id'     => $classId,
    'subject_id'   => (int) ($input['subject_id'] ?? 0),
    'teacher_id'   => $input['teacher_id'] ? (int) $input['teacher_id'] : null,
    'day_of_week'  => $day,
    'period'       => $period,
    'starts_at'    => $input['starts_at'] ?: TimetableManager::PERIODS[$period]['starts_at'] ?? '08:00',
    'ends_at'      => $input['ends_at'] ?: TimetableManager::PERIODS[$period]['ends_at'] ?? '08:40',
    'room'         => $input['room'] ?: null,
    'session_name' => $input['session_name'] ?: current_session_name(),
    'term'         => $input['term'] ?: current_term(),
]);

AuditLogger::log('timetable.save', 'class', (string) $classId, $day . ' period ' . $period);
flash('Timetable slot saved. It repeats every week for the term.');
redirect('/admin/academics/timetable.php?class_id=' . $classId . '&day=' . urlencode($day));
