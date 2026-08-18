<?php
$pageTitle = 'About Us';
$activeNav = 'about';
require __DIR__ . '/../partials/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · About us</div>
        <h1>About Plus International School</h1>
        <p>A nursery, primary and secondary school in Tunga, Minna, Niger State, committed to knowledge, character and excellence.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="reveal">
            <h2>Our story</h2>
            <p class="muted">Plus International School was founded to give families in Minna a school where academic rigour and genuine care for the child sit side by side. From a small nursery class, the school has grown into a full nursery, primary and secondary institution with modern facilities and a fully digital administration.</p>
            <p class="muted">Today our students consistently excel in internal assessments, WAEC, NECO and national competitions, while remaining grounded in discipline, integrity and service.</p>
        </div>
        <div class="video-frame reveal"><div class="placeholder">Image placeholder — assets/images/campus/campus-main.jpg</div></div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="grid grid-3">
            <div class="card reveal"><div class="icon">🎯</div><h3>Our mission</h3><p>To nurture confident, disciplined and globally competitive learners through excellent teaching, strong values and modern facilities.</p></div>
            <div class="card reveal reveal-delay-1"><div class="icon">🔭</div><h3>Our vision</h3><p>To be the reference school in Niger State for academic excellence, character formation and digital innovation.</p></div>
            <div class="card reveal reveal-delay-2"><div class="icon">💎</div><h3>Our core values</h3><p>Knowledge, character, excellence, integrity, service and respect for every child's individuality.</p></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title reveal"><span>Leadership</span><h2>Meet the school leadership</h2></div>
        <div class="grid grid-4">
            <?php foreach ([
                ['Proprietor', 'Photo placeholder'],
                ['Principal', 'Photo placeholder'],
                ['Head of Primary', 'Photo placeholder'],
                ['Head of Nursery', 'Photo placeholder'],
            ] as [$role, $caption]): ?>
                <div class="card program-card reveal">
                    <div class="media"><div class="placeholder"><?= e($caption) ?></div></div>
                    <div class="body"><h3><?= e($role) ?></h3><p class="muted">Name to be uploaded by the administrator.</p></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
