<?php
$pageTitle = 'Admission Requirements';
$activeNav = 'admissions';
require __DIR__ . '/../partials/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Admissions · Requirements</div>
        <h1>Admission requirements</h1>
        <p>What to prepare before you apply.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="reveal">
            <h2>Documents required</h2>
            <ul class="checklist">
                <li>Birth certificate or declaration of age</li>
                <li>Two recent passport photographs</li>
                <li>Immunisation record (nursery and primary entrants)</li>
                <li>Last two terms' result sheets from the previous school</li>
                <li>Transfer certificate or testimonial (transferring students)</li>
                <li>Parent/guardian means of identification</li>
            </ul>
        </div>
        <div class="reveal">
            <h2>Age requirements</h2>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Class</th><th>Minimum age</th></tr></thead>
                    <tbody>
                        <tr><td>Creche / Pre-Nursery</td><td>2 years</td></tr>
                        <tr><td>Nursery 1</td><td>3 years</td></tr>
                        <tr><td>Nursery 2</td><td>4 years</td></tr>
                        <tr><td>Primary 1</td><td>5 years</td></tr>
                        <tr><td>JSS 1</td><td>10 years</td></tr>
                        <tr><td>SS 1</td><td>13 years</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="muted mt-2">Students transferring mid-session are placed after an assessment.</p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
