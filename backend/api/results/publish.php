<?php
require_once dirname(__DIR__, 3) . '/config.php';

Auth::requirePermission('manage_results');
$input = request_input();
$classId = (int) ($input['class_id'] ?? 0);
$term = (string) ($input['term'] ?? current_term());
$sessionName = (string) ($input['session_name'] ?? current_session_name());
$publish = ($input['publish'] ?? '1') === '0' ? false : true;

if (!$classId) {
    json_response(['ok' => false, 'error' => 'Select a class.'], 422);
}

ResultCalculator::computeClass($classId, $sessionName, $term);
ResultCalculator::publish($classId, $sessionName, $term, $publish);

$students = Database::all("SELECT id FROM users WHERE class_id = ? AND role = 'student'", [$classId]);
if ($publish) {
    foreach ($students as $student) {
        NotificationSystem::send(
            (int) $student['id'],
            'Your result is ready',
            $term . ' term result for the ' . $sessionName . ' session has been published.'
        );
    }
}

flash($publish ? 'Results published for ' . count($students) . ' students.' : 'Results unpublished.');
redirect('/admin/academics/results.php');
