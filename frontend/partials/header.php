<?php
/** Public site header. Set $pageTitle, $pageDescription and $activeNav before including. */
require_once dirname(__DIR__, 2) . '/config.php';

$pageTitle = $pageTitle ?? SCHOOL_NAME;
$pageDescription = $pageDescription ?? SCHOOL_NAME . ' — a nursery, primary and secondary school in Tunga, Minna, Niger State.';
$activeNav = $activeNav ?? '';
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · <?= e(SCHOOL_NAME) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<link rel="icon" href="<?= url('assets/images/profiles/logo.png') ?>">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/animations.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
<?php foreach (($extraCss ?? []) as $css): ?>
<link rel="stylesheet" href="<?= url('assets/css/' . $css) ?>">
<?php endforeach; ?>
<script>window.APP_URL = <?= json_encode(APP_URL) ?>;</script>
</head>
<body>
<div class="topbar">
    <div class="container">
        <div class="topbar-contact">
            <span>📍 <?= e(SCHOOL_ADDRESS) ?></span>
            <a href="tel:<?= e(SCHOOL_PHONE) ?>">📞 <?= e(SCHOOL_PHONE) ?></a>
            <a href="mailto:<?= e(SCHOOL_EMAIL) ?>">✉ <?= e(SCHOOL_EMAIL) ?></a>
        </div>
        <div class="flex gap-1">
            <a href="<?= url('chat/login.php') ?>">💬 Chat</a>
            <?php if ($currentUser): ?>
                <a href="<?= Auth::dashboardFor($currentUser['role']) ?>">My dashboard</a>
            <?php else: ?>
                <a href="<?= url('portal/login.php') ?>">Portal login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="container">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">PIS</span>
            <span class="brand-text">
                <strong>Plus International School</strong>
                <small>Tunga · Minna · Niger State</small>
            </span>
        </a>

        <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>

        <nav class="nav">
            <a href="<?= url('index.php') ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Home</a>
            <a href="<?= url('frontend/public/about.php') ?>" class="<?= $activeNav === 'about' ? 'active' : '' ?>">About</a>
            <div class="dropdown">
                <a href="#" class="<?= $activeNav === 'academics' ? 'active' : '' ?>">Academics ▾</a>
                <div class="dropdown-menu">
                    <a href="<?= url('frontend/academics/nursery.php') ?>">Nursery School</a>
                    <a href="<?= url('frontend/academics/primary.php') ?>">Primary School</a>
                    <a href="<?= url('frontend/academics/secondary.php') ?>">Secondary School</a>
                    <a href="<?= url('frontend/information/calendar.php') ?>">Academic Calendar</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="<?= $activeNav === 'admissions' ? 'active' : '' ?>">Admissions ▾</a>
                <div class="dropdown-menu">
                    <a href="<?= url('frontend/admissions/admission.php') ?>">Admission Process</a>
                    <a href="<?= url('frontend/admissions/requirements.php') ?>">Requirements</a>
                    <a href="<?= url('frontend/admissions/apply-online.php') ?>">Apply Online</a>
                    <a href="<?= url('frontend/admissions/fees.php') ?>">Fee Structure</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="<?= $activeNav === 'info' ? 'active' : '' ?>">School Life ▾</a>
                <div class="dropdown-menu">
                    <a href="<?= url('frontend/information/facilities.php') ?>">Facilities</a>
                    <a href="<?= url('frontend/information/gallery.php') ?>">Gallery</a>
                    <a href="<?= url('frontend/information/news.php') ?>">News &amp; Events</a>
                </div>
            </div>
            <a href="<?= url('portal/check-result.php') ?>" class="<?= $activeNav === 'results' ? 'active' : '' ?>">Check Result</a>
            <a href="<?= url('frontend/public/contact.php') ?>" class="<?= $activeNav === 'contact' ? 'active' : '' ?>">Contact</a>
            <a class="btn btn-primary btn-sm" href="<?= url('portal/login.php') ?>">Portal</a>
        </nav>
    </div>
</header>
