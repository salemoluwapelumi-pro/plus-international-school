<?php
/** Student self-registration. The admission number must already exist in the school records. */
require_once dirname(__DIR__) . '/config.php';

if (Auth::check()) {
    redirect(Auth::DASHBOARDS[Auth::role()] ?? '/portal/index.php');
}
$classes = classes_list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create a student account · <?= e(SCHOOL_NAME) ?></title>
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
            <span class="brand-text"><strong><?= e(SCHOOL_NAME) ?></strong><small>Student registration</small></span>
        </a>
        <h2 class="mt-3">Create your student account</h2>
        <p>Your admission number links your account to your permanent school record, so your results and payment history follow you from year to year.</p>
        <ul class="checklist">
            <li>Check results as soon as they are published</li>
            <li>Pay school fees and download receipts</li>
            <li>See your class timetable and assignments</li>
        </ul>
    </aside>

    <main class="auth-panel">
        <div class="auth-card" style="max-width:520px">
            <h1>Student registration</h1>
            <p class="muted">All fields marked * are required.</p>

            <div id="authMessage" style="display:none"></div>

            <form id="studentRegisterForm" novalidate>
                <div class="form-grid">
                    <div class="field field-full"><label for="r-name">Full name *</label><input id="r-name" name="full_name" required></div>
                    <div class="field"><label for="r-admission">Admission number *</label><input id="r-admission" name="admission_no" placeholder="PIS/2024/0123" required></div>
                    <div class="field"><label for="r-email">Email address *</label><input id="r-email" type="email" name="email" required></div>
                    <div class="field"><label for="r-phone">Phone number</label><input id="r-phone" name="phone"></div>
                    <div class="field">
                        <label for="r-gender">Gender</label>
                        <select id="r-gender" name="gender"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option></select>
                    </div>
                    <div class="field">
                        <label for="r-class">Class *</label>
                        <select id="r-class" name="class_id" required>
                            <option value="">Select class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= (int) $class['id'] ?>"><?= e($class['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label for="r-dob">Date of birth</label><input id="r-dob" type="date" name="date_of_birth"></div>
                    <div class="field"><label for="r-password">Password *</label><input id="r-password" type="password" name="password" minlength="6" required></div>
                    <div class="field"><label for="r-password2">Confirm password *</label><input id="r-password2" type="password" name="password_confirm" minlength="6" required></div>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Create account</button>
            </form>

            <p class="text-center mt-2">Already have an account? <a href="<?= url('portal/login.php') ?>">Sign in</a></p>
        </div>
    </main>
</div>

<script src="<?= url('assets/js/main.js') ?>"></script>
<script src="<?= url('assets/js/auth.js') ?>"></script>
</body>
</html>
