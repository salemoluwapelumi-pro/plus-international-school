<?php
/**
 * Dashboard shell (purple theme) shared by every signed-in role.
 * Set $pageTitle, $pageSubtitle and $activeMenu before including.
 */
require_once dirname(__DIR__, 3) . '/config.php';

$user = Auth::requireLogin();
$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? date('l, j F Y');
$activeMenu = $activeMenu ?? '';
$unread = NotificationSystem::unreadCount($user);
$pendingPayments = Auth::can('view_payments')
    ? (int) Database::value("SELECT COUNT(*) FROM payment_transactions WHERE status IN ('submitted','verified')")
    : 0;

/** One sidebar entry. */
function side_link(string $key, string $href, string $icon, string $label, string $active, ?int $pill = null): void
{
    printf(
        '<a class="side-link %s" href="%s"><span class="ico">%s</span><span>%s</span>%s</a>',
        $key === $active ? 'active' : '',
        e(url($href)),
        $icon,
        e($label),
        $pill ? '<span class="pill">' . $pill . '</span>' : ''
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/animations.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/purple-theme.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
<script>window.APP_URL = <?= json_encode(APP_URL) ?>;</script>
</head>
<body class="dash-body">
<aside class="dash-sidebar">
    <a class="brand" href="<?= url('index.php') ?>">
        <span class="brand-mark">PIS</span>
        <span class="brand-text"><strong>Plus International</strong><small><?= e(ucfirst($user['role'])) ?> workspace</small></span>
    </a>

    <?php if (Auth::is('superadmin', 'subadmin')): ?>
        <div class="side-group">
            <span>Overview</span>
            <?php side_link('dashboard', 'admin/dashboard.php', '▦', 'Dashboard', $activeMenu); ?>
            <?php if (Auth::can('view_audit_log')) side_link('audit', 'admin/system/audit-log.php', '🕓', 'Audit log', $activeMenu); ?>
        </div>
        <?php if (Auth::can('manage_users')): ?>
        <div class="side-group">
            <span>People</span>
            <?php side_link('users', 'admin/users/manage.php', '👥', 'User accounts', $activeMenu); ?>
            <?php side_link('permissions', 'admin/users/permissions.php', '🔐', 'Permissions', $activeMenu); ?>
        </div>
        <?php endif; ?>
        <div class="side-group">
            <span>Academics</span>
            <?php side_link('results', 'admin/academics/results.php', '📊', 'Results', $activeMenu); ?>
            <?php side_link('records', 'admin/academics/records.php', '🗄', 'Student records', $activeMenu); ?>
            <?php side_link('timetable', 'admin/academics/timetable.php', '📅', 'Timetable', $activeMenu); ?>
            <?php side_link('classes', 'admin/academics/classes.php', '🏫', 'Classes &amp; subjects', $activeMenu); ?>
        </div>
        <div class="side-group">
            <span>Finance</span>
            <?php if (Auth::can('view_payments')) side_link('payments', 'admin/payments/overview.php', '💰', 'Payments', $activeMenu, $pendingPayments ?: null); ?>
            <?php if (Auth::can('view_payments')) side_link('class-payments', 'admin/payments/class-payments.php', '📋', 'Class by class', $activeMenu); ?>
            <?php if (Auth::can('manage_fees')) side_link('fees', 'admin/payments/fees.php', '🏷', 'Fee structure', $activeMenu); ?>
        </div>
        <div class="side-group">
            <span>School</span>
            <?php side_link('announcements', 'admin/system/announcements.php', '📢', 'Announcements', $activeMenu); ?>
            <?php side_link('admissions', 'admin/system/admissions.php', '📝', 'Applications', $activeMenu); ?>
            <?php side_link('settings', 'admin/system/settings.php', '⚙', 'Settings', $activeMenu); ?>
        </div>
    <?php elseif (Auth::is('cashier')): ?>
        <div class="side-group">
            <span>Cashier</span>
            <?php side_link('dashboard', 'cashier/dashboard.php', '▦', 'Dashboard', $activeMenu); ?>
            <?php side_link('verify', 'cashier/verify-payments.php', '✅', 'Approve payments', $activeMenu, $pendingPayments ?: null); ?>
            <?php side_link('receipts', 'cashier/receipts.php', '🧾', 'Receipts', $activeMenu); ?>
            <?php side_link('class-payments', 'cashier/class-payments.php', '📋', 'Class by class', $activeMenu); ?>
            <?php side_link('daily', 'cashier/daily-report.php', '📈', 'Daily report', $activeMenu); ?>
        </div>
    <?php elseif (Auth::is('teacher')): ?>
        <div class="side-group">
            <span>Teaching</span>
            <?php side_link('dashboard', 'teacher/dashboard.php', '▦', 'Dashboard', $activeMenu); ?>
            <?php side_link('upload-results', 'teacher/academics/upload-results.php', '📝', 'Upload results', $activeMenu); ?>
            <?php side_link('attendance', 'teacher/academics/attendance.php', '🗓', 'Attendance', $activeMenu); ?>
            <?php side_link('assignments', 'teacher/academics/assignments.php', '📚', 'Assignments', $activeMenu); ?>
            <?php side_link('timetable', 'teacher/profile/timetable.php', '📅', 'My timetable', $activeMenu); ?>
        </div>
    <?php elseif (Auth::is('student')): ?>
        <div class="side-group">
            <span>My school</span>
            <?php side_link('dashboard', 'student/dashboard.php', '▦', 'Dashboard', $activeMenu); ?>
            <?php side_link('results', 'student/academics/results.php', '📊', 'My results', $activeMenu); ?>
            <?php side_link('timetable', 'student/academics/timetable.php', '📅', 'Timetable', $activeMenu); ?>
            <?php side_link('assignments', 'student/academics/assignments.php', '📚', 'Assignments', $activeMenu); ?>
        </div>
        <div class="side-group">
            <span>Fees</span>
            <?php side_link('pay', 'student/payments/pay.php', '💳', 'Pay school fees', $activeMenu); ?>
            <?php side_link('history', 'student/payments/history.php', '🧾', 'Payment history', $activeMenu); ?>
        </div>
    <?php elseif (Auth::is('parent')): ?>
        <div class="side-group">
            <span>My children</span>
            <?php side_link('dashboard', 'parent/dashboard.php', '▦', 'Dashboard', $activeMenu); ?>
            <?php side_link('results', 'parent/children/results.php', '📊', 'Results', $activeMenu); ?>
            <?php side_link('payments', 'parent/children/payments.php', '💳', 'Pay fees', $activeMenu); ?>
        </div>
    <?php endif; ?>

    <div class="side-group">
        <span>Communication</span>
        <?php side_link('chat', 'chat/login.php', '💬', 'Chat system', $activeMenu); ?>
        <?php side_link('profile', 'portal/profile.php', '👤', 'My profile', $activeMenu); ?>
        <a class="side-link" href="<?= url('backend/api/auth/logout.php') ?>"><span class="ico">⏻</span><span>Sign out</span></a>
    </div>
</aside>

<div class="dash-main">
    <div class="dash-topbar">
        <div class="flex gap-1" style="align-items:center">
            <button class="menu-btn" aria-label="Toggle menu">☰</button>
            <div>
                <h1><?= e($pageTitle) ?></h1>
                <div class="sub"><?= e($pageSubtitle) ?></div>
            </div>
        </div>
        <div class="topbar-actions">
            <div class="user-menu">
                <button class="bell" id="notificationBell" aria-label="Notifications">🔔
                    <span class="dot" style="<?= $unread ? '' : 'display:none' ?>"><?= $unread ?></span>
                </button>
                <div class="menu" id="notificationPanel"></div>
            </div>
            <div class="user-menu">
                <button data-user-menu style="background:none;border:0;cursor:pointer" class="flex gap-1" aria-label="Account menu">
                    <span class="avatar"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></span>
                </button>
                <div class="menu">
                    <a href="<?= url('portal/profile.php') ?>"><strong><?= e($user['full_name']) ?></strong><br><small><?= e(ucfirst($user['role'])) ?></small></a>
                    <a href="<?= url('portal/profile.php') ?>">My profile</a>
                    <a href="<?= url('index.php') ?>">Visit website</a>
                    <a href="<?= url('backend/api/auth/logout.php') ?>">Sign out</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-content">
        <?php foreach (take_flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
