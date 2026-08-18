<?php
require_once dirname(__DIR__) . '/config.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$message = null;
$messageType = 'error';
$valid = $token !== '' && PasswordReset::userForToken($token) !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 6) {
        $message = 'The password must be at least 6 characters long.';
    } elseif ($password !== ($_POST['password_confirm'] ?? '')) {
        $message = 'The two passwords do not match.';
    } elseif (PasswordReset::complete($token, $password)) {
        $message = 'Your password has been changed. You can now sign in.';
        $messageType = 'success';
        $valid = false;
    } else {
        $message = 'This reset link is invalid or has expired.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Choose a new password · <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
</head>
<body>
<div class="auth-wrap">
    <aside class="auth-aside">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">PIS</span>
            <span class="brand-text"><strong><?= e(SCHOOL_NAME) ?></strong><small>Account recovery</small></span>
        </a>
        <h2 class="mt-3">Choose a new password</h2>
        <p>Pick something you have not used before. You will be signed out of other sessions.</p>
    </aside>
    <main class="auth-panel">
        <div class="auth-card">
            <h1>New password</h1>
            <?php if ($message): ?>
                <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
            <?php endif; ?>

            <?php if ($valid): ?>
                <form method="post">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="field"><label for="password">New password</label><input id="password" type="password" name="password" minlength="6" required></div>
                    <div class="field"><label for="password2">Confirm new password</label><input id="password2" type="password" name="password_confirm" minlength="6" required></div>
                    <button class="btn btn-primary btn-block" type="submit">Change password</button>
                </form>
            <?php elseif ($messageType !== 'success'): ?>
                <div class="alert alert-error">This reset link is invalid or has expired. Please request a new one.</div>
                <a class="btn btn-ghost btn-block" href="<?= url('portal/forgot-password.php') ?>">Request a new link</a>
            <?php endif; ?>

            <p class="text-center mt-2"><a href="<?= url('portal/login.php') ?>">← Back to sign in</a></p>
        </div>
    </main>
</div>
</body>
</html>
