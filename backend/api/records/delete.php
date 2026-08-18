<?php
require_once dirname(__DIR__, 3) . '/config.php';

Auth::requirePermission('manage_records');
$input = request_input();
$id = (int) ($input['id'] ?? 0);
$record = Database::one('SELECT * FROM student_records WHERE id = ?', [$id]);

if ($record) {
    Database::run('DELETE FROM student_records WHERE id = ?', [$id]);
    AuditLogger::log('record.delete', 'student_record', (string) $id, $record['title']);
    flash('Record deleted.');
}

redirect('/admin/academics/records.php' . ($record ? '?student_id=' . $record['student_id'] : ''));
