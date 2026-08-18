<?php
$pageTitle = 'Facilities';
$activeNav = 'info';
require __DIR__ . '/../partials/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · School life · Facilities</div>
        <h1>Our facilities</h1>
        <p>Purpose-built spaces that make learning practical, safe and enjoyable.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <?php foreach ([
                ['Digital library', 'Over 8,000 titles, e-readers and a quiet study hall.'],
                ['Science laboratories', 'Separate physics, chemistry and biology laboratories.'],
                ['ICT & robotics lab', '40 workstations, 3D printer and robotics kits.'],
                ['Sports complex', 'Football pitch, basketball court and indoor sports hall.'],
                ['Performing arts theatre', 'A 400-seat hall for drama, music and assemblies.'],
                ['School clinic', 'Resident nurse, sick bay and emergency response plan.'],
                ['Dining hall', 'Balanced, freshly prepared meals supervised by a nutritionist.'],
                ['Transport fleet', 'GPS-tracked buses with trained drivers and attendants.'],
                ['Boarding house', 'Well-supervised hostels with study prep and matrons.'],
            ] as [$title, $text]): ?>
                <div class="card program-card reveal">
                    <div class="media"><div class="placeholder">Image placeholder — <?= e($title) ?></div></div>
                    <div class="body"><h3><?= e($title) ?></h3><p><?= e($text) ?></p></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
