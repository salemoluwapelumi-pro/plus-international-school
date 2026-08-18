<?php
/** Adds a permanent student record entry, with an optional uploaded document. */
require_once dirname(__DIR__, 3) . '/config.php';

$actor = Auth::requirePermission('manage_records');
$input = request_input();
$studentId = (int) ($input['student_id'] ?? 0);
$title = trim((string) ($input['title'] ?? ''));

if (!$studentId || $title === '') {
    flash('Choose a student and give the record a title.', 'error');
    redirect('/admin/academics/records.php');
}

$fields = [
    'student_id'   => $studentId,
    'record_type'  => $input['record_type'] ?: 'other',
    'title'        => $title,
    'description'  => $input['description'] ?: null,
    'session_name' => $input['session_name'] ?: current_session_name(),
    'uploaded_by'  => (int) $actor['id'],
];

$file = store_upload('document', 'records');
if ($file) {
    $fields['file_path'] = $file;
}

$id = (int) ($input['id'] ?? 0);
if ($id) {
    Database::updateRow('student_records', $id, $fields);
    AuditLogger::log('record.update', 'student_record', (string) $id, $title);
    flash('Record updated.');
} else {
    $newId = Database::insertRow('student_records', $fields);
    AuditLogger::log('record.create', 'student_record', (string) $newId, $title);
    flash('Record added.');
}

redirect('/admin/academics/records.php?student_id=' . $studentId);
