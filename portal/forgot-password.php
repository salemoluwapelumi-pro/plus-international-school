<?php
require_once dirname(__DIR__) . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot password · <?= e(SCHOOL_NAME) ?></title>
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
            <span class="brand-text"><strong><?= e(SCHOOL_NAME) ?></strong><small>Account recovery</small></span>
        </a>
        <h2 class="mt-3">Forgot your password?</h2>
        <p>Enter the email address on your account and we will send you a secure link to choose a new password. The link expires after one hour.</p>
    </aside>
    <main class="auth-panel">
        <div class="auth-card">
            <h1>Reset your password</h1>
            <p class="muted">We will email you a reset link.</p>
            <div id="authMessage" style="display:none"></div>
            <form id="forgotForm" novalidate>
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" required>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Send reset link</button>
            </form>
            <div class="alert alert-warning mt-2" id="resetLinkHint" style="display:none"></div>
            <p class="text-center mt-2"><a href="<?= url('portal/login.php') ?>">← Back to sign in</a></p>
        </div>
    </main>
</div>
<script src="<?= url('assets/js/main.js') ?>"></script>
<script src="<?= url('assets/js/auth.js') ?>"></script>
</body>
</html>
