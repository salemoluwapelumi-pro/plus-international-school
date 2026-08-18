<?php
/**
 * The single sign-in page for the whole school: super admin, sub-admin,
 * cashier, teacher, student and parent all use this form. Nothing on the page
 * reveals that administrators sign in here too — the role comes from the
 * database and decides which dashboard the user lands on.
 */
require_once dirname(__DIR__) . '/config.php';

if (Auth::check()) {
    redirect(Auth::DASHBOARDS[Auth::role()] ?? '/portal/index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Portal Login · <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/animations.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
<script>window.APP_URL = <?= json_encode(APP_URL) ?>;</script>
</head>
<body>
<div class="auth-wrap">
    <aside class="auth-aside">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">PIS</span>
            <span class="brand-text"><strong><?= e(SCHOOL_NAME) ?></strong><small><?= e(SCHOOL_ADDRESS) ?></small></span>
        </a>
        <h2 class="mt-3">Welcome back to the school portal</h2>
        <p>Sign in to check results, pay school fees, view the timetable and stay connected with the school.</p>
        <ul class="checklist">
            <li>Instant termly results with grades and positions</li>
            <li>Secure online fee payment and receipts</li>
            <li>Weekly timetable, assignments and announcements</li>
            <li>Teacher–student chat support</li>
        </ul>
    </aside>

    <main class="auth-panel">
        <div class="auth-card">
            <h1>Sign in</h1>
            <p class="muted">Use your email address, admission number or staff number.</p>

            <div id="authMessage" style="display:none"></div>

            <form id="loginForm" novalidate>
                <div class="field">
                    <label for="identifier">Email / admission number</label>
                    <input id="identifier" name="identifier" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    <small><button type="button" class="btn-link" data-toggle-password="password" style="background:none;border:0;color:var(--blue);cursor:pointer;padding:0">Show</button> password</small>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Sign in</button>
            </form>

            <div class="flex-between mt-2">
                <a href="<?= url('portal/forgot-password.php') ?>">Forgot password?</a>
                <a href="<?= url('portal/register.php') ?>">Create a student account</a>
            </div>

            <div class="alert alert-info mt-3">
                Students need their <strong>admission number</strong> to create an account. Staff accounts are created by the school administrator.
            </div>

            <p class="text-center mt-2"><a href="<?= url('index.php') ?>">← Back to the website</a></p>
        </div>
    </main>
</div>

<script src="<?= url('assets/js/main.js') ?>"></script>
<script src="<?= url('assets/js/auth.js') ?>"></script>
</body>
</html>
