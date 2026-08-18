<?php
$pageTitle = 'Gallery';
$activeNav = 'info';
require __DIR__ . '/../partials/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · School life · Gallery</div>
        <h1>Photo &amp; video gallery</h1>
        <p>Moments from classrooms, laboratories, the sports field and school events.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="gallery">
            <?php foreach ([
                'Morning assembly', 'Science practical', 'Inter-house sports', 'Robotics club',
                'Cultural day', 'Graduation ceremony', 'Digital library', 'Excursion to Zuma Rock',
                'Debate competition', 'Prize giving day', 'Music class', 'Founders day',
            ] as $caption): ?>
                <div class="gallery-item reveal" data-caption="<?= e($caption) ?>">
                    <div class="placeholder"><?= e($caption) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-title mt-3"><span>Videos</span><h2>Watch the school in action</h2></div>
        <div class="grid grid-2">
            <div class="video-frame reveal"><div class="placeholder">Video placeholder — campus tour</div></div>
            <div class="video-frame reveal"><div class="placeholder">Video placeholder — graduation highlights</div></div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
