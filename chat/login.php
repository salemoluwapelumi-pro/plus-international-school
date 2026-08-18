<?php
require_once dirname(__DIR__) . '/config.php';

if (ChatSystem::user()) {
    redirect('/chat/app/index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Chat login · <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
<script>window.APP_URL = <?= json_encode(APP_URL) ?>;</script>
</head>
<body>
<div class="auth-wrap">
    <aside class="auth-aside">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">PIS</span>
            <span class="brand-text"><strong>School chat</strong><small>Teachers &amp; students</small></span>
        </a>
        <h2 class="mt-3">Ask your teacher anything</h2>
        <p>The school chat is a separate, moderated space where students can ask questions and teachers can answer them outside class hours.</p>
    </aside>
    <main class="auth-panel">
        <div class="auth-card">
            <h1>Chat sign in</h1>
            <div id="authMessage" style="display:none"></div>
            <form data-ajax-form action="<?= url('backend/api/chat/login.php') ?>">
                <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" required></div>
                <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required></div>
                <button class="btn btn-primary btn-block" type="submit">Sign in to chat</button>
            </form>
            <p class="text-center mt-2">New here? <a href="<?= url('chat/register.php') ?>">Create a chat account</a></p>
            <p class="text-center"><a href="<?= url('index.php') ?>">← Back to the website</a></p>
        </div>
    </main>
</div>
<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
