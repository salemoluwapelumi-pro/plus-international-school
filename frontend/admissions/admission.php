<?php
$pageTitle = 'Admission Process';
$activeNav = 'admissions';
require __DIR__ . '/../partials/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Admissions</div>
        <h1>Admission process</h1>
        <p>Five simple steps from your first enquiry to your child's first day at Plus International School.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <?php
            $steps = [
                ['1', 'Apply online', 'Complete the online application form with your child\'s details. It takes about five minutes.'],
                ['2', 'Pay the application fee', 'Pay the non-refundable application fee through the portal with Paystack, Remita or bank transfer.'],
                ['3', 'Entrance assessment', 'Your child sits a short age-appropriate assessment in English and Mathematics (nursery entrants are interviewed instead).'],
                ['4', 'Interview & offer', 'Parents meet the admissions team. Successful candidates receive an offer letter with the fee schedule.'],
                ['5', 'Accept and pay fees', 'Accept the offer and pay the first term fees online. An admission number is generated for your child.'],
                ['6', 'Create a portal account', 'Use the admission number to create a student portal account for results, fees and chat.'],
            ];
            foreach ($steps as [$number, $title, $text]): ?>
                <div class="card reveal">
                    <div class="icon"><?= e($number) ?></div>
                    <h3><?= e($title) ?></h3>
                    <p><?= e($text) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="<?= url('frontend/admissions/apply-online.php') ?>">Start your application</a>
            <a class="btn btn-ghost" href="<?= url('frontend/admissions/requirements.php') ?>">See requirements</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
