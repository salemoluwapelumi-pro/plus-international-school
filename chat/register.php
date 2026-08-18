<?php
require_once dirname(__DIR__) . '/config.php';

if (ChatSystem::user()) {
    redirect('/chat/app/index.php');
}
$classes = classes_list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Chat registration · <?= e(SCHOOL_NAME) ?></title>
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
        <h2 class="mt-3">Create your chat account</h2>
        <p>Students register with the admission number issued by the school. Teachers register with their staff number.</p>
    </aside>
    <main class="auth-panel">
        <div class="auth-card" style="max-width:560px">
            <h1>Chat registration</h1>
            <div id="authMessage" style="display:none"></div>
            <form data-ajax-form action="<?= url('backend/api/chat/register.php') ?>">
                <div class="form-grid">
                    <div class="field field-full">
                        <label for="c-role">I am a *</label>
                        <select id="c-role" name="role" required>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    <div class="field field-full"><label for="c-name">Full name *</label><input id="c-name" name="full_name" required></div>
                    <div class="field"><label for="c-email">Email address *</label><input id="c-email" type="email" name="email" required></div>
                    <div class="field"><label for="c-phone">Phone number *</label><input id="c-phone" name="phone" required></div>
                    <div class="field"><label for="c-admission">Admission number</label><input id="c-admission" name="admission_no" placeholder="Students only"></div>
                    <div class="field"><label for="c-staff">Staff number</label><input id="c-staff" name="staff_no" placeholder="Teachers only"></div>
                    <div class="field">
                        <label for="c-gender">Gender</label>
                        <select id="c-gender" name="gender"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option></select>
                    </div>
                    <div class="field"><label for="c-age">Age</label><input id="c-age" type="number" name="age" min="2" max="80"></div>
                    <div class="field field-full">
                        <label for="c-class">Class</label>
                        <select id="c-class" name="class_id">
                            <option value="">Select class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= (int) $class['id'] ?>"><?= e($class['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field field-full"><label for="c-password">Password *</label><input id="c-password" type="password" name="password" minlength="6" required></div>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Create chat account</button>
            </form>
            <p class="text-center mt-2">Already registered? <a href="<?= url('chat/login.php') ?>">Sign in</a></p>
        </div>
    </main>
</div>
<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
