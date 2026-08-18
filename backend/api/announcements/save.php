<?php
require_once dirname(__DIR__, 3) . '/config.php';

$actor = Auth::requirePermission('manage_announcements');
$input = request_input();
$title = trim((string) ($input['title'] ?? ''));
$body = trim((string) ($input['body'] ?? ''));
$audience = in_array($input['audience'] ?? '', ['public', 'students', 'parents', 'staff', 'all'], true) ? $input['audience'] : 'public';

if ($title === '' || $body === '') {
    flash('A title and a message are required.', 'error');
    redirect('/admin/system/announcements.php');
}

Database::insertRow('announcements', [
    'title'      => $title,
    'body'       => $body,
    'audience'   => $audience,
    'created_by' => (int) $actor['id'],
]);

$audienceKey = match ($audience) {
    'students' => 'student',
    'parents'  => 'parent',
    'staff'    => 'staff',
    'all'      => 'all',
    default    => '',
};
if ($audienceKey !== '') {
    NotificationSystem::broadcast($audienceKey, $title, mb_substr($body, 0, 160));
}

AuditLogger::log('announcement.create', 'announcement', '', $title);
flash('Announcement published.');
redirect('/admin/system/announcements.php');
