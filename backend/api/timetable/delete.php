<?php
require_once dirname(__DIR__, 3) . '/config.php';

Auth::requirePermission('manage_timetable');
$input = request_input();
$id = (int) ($input['id'] ?? 0);
$classId = (int) ($input['class_id'] ?? 0);

TimetableManager::delete($id);
AuditLogger::log('timetable.delete', 'slot', (string) $id);
flash('Timetable slot removed.');
redirect('/admin/academics/timetable.php' . ($classId ? '?class_id=' . $classId : ''));
