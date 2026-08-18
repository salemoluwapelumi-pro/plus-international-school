<?php
/** Saves a class score sheet, then recomputes totals, grades, averages and positions. */
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::requirePermission('manage_results');
$input = request_input();

$classId = (int) ($input['class_id'] ?? 0);
$subjectId = (int) ($input['subject_id'] ?? 0);
$term = (string) ($input['term'] ?? current_term());
$sessionName = (string) ($input['session_name'] ?? current_session_name());
$studentIds = (array) ($input['student_id'] ?? []);

if (!$classId || !$subjectId || !$studentIds) {
    flash('Select a class and subject before saving.', 'error');
    redirect('/teacher/academics/upload-results.php');
}

foreach (array_values($studentIds) as $index => $studentId) {
    ResultCalculator::saveScore([
        'student_id'   => (int) $studentId,
        'class_id'     => $classId,
        'subject_id'   => $subjectId,
        'session_name' => $sessionName,
        'term'         => $term,
        'ca1'          => (float) ($input['ca1'][$index] ?? 0),
        'ca2'          => (float) ($input['ca2'][$index] ?? 0),
        'assignment'   => (float) ($input['assignment'][$index] ?? 0),
        'exam'         => (float) ($input['exam'][$index] ?? 0),
    ], (int) $user['id']);
}

ResultCalculator::computeClass($classId, $sessionName, $term);
AuditLogger::log('results.save', 'class', (string) $classId, 'subject ' . $subjectId . ' — ' . count($studentIds) . ' students');

flash('Scores saved. Totals, grades and positions have been recomputed.');
redirect('/teacher/academics/upload-results.php?class_id=' . $classId . '&subject_id=' . $subjectId . '&term=' . urlencode($term));
