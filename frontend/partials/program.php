<?php
/** Shared layout for the nursery / primary / secondary programme pages. */
require __DIR__ . '/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Academics · <?= e($program['name']) ?></div>
        <h1><?= e($program['name']) ?></h1>
        <p><?= e($program['intro']) ?></p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="reveal">
            <h2>What your child learns</h2>
            <p class="muted"><?= e($program['description']) ?></p>
            <ul class="checklist">
                <?php foreach ($program['highlights'] as $highlight): ?>
                    <li><?= e($highlight) ?></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-primary mt-2" href="<?= url('frontend/admissions/apply-online.php') ?>">Apply for admission</a>
        </div>
        <div class="video-frame reveal"><div class="placeholder"><?= e($program['media']) ?></div></div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-title reveal"><span>Curriculum</span><h2>Subjects offered</h2></div>
        <div class="grid grid-4">
            <?php foreach ($program['subjects'] as $subject): ?>
                <div class="card reveal"><h3 style="font-size:1rem;margin:0"><?= e($subject) ?></h3></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <div class="card reveal"><div class="icon">🕗</div><h3>School day</h3><p><?= e($program['day']) ?></p></div>
            <div class="card reveal reveal-delay-1"><div class="icon">👩‍🏫</div><h3>Class size</h3><p><?= e($program['class_size']) ?></p></div>
            <div class="card reveal reveal-delay-2"><div class="icon">🏆</div><h3>Assessment</h3><p>Continuous assessment (30 marks) plus a termly examination (70 marks), computed automatically on the school portal.</p></div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
