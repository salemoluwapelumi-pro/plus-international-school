<?php
$pageTitle = 'Contact Us';
$activeNav = 'contact';
require __DIR__ . '/../partials/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Contact</div>
        <h1>Contact the school</h1>
        <p>We are happy to answer your questions about admissions, fees, transport or anything else.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="reveal">
            <h2>Send us a message</h2>
            <form data-ajax action="<?= url('backend/api/contact.php') ?>" method="post">
                <div class="form-grid">
                    <div class="field"><label for="c-name">Full name</label><input id="c-name" name="name" required></div>
                    <div class="field"><label for="c-email">Email address</label><input id="c-email" type="email" name="email" required></div>
                    <div class="field"><label for="c-phone">Phone number</label><input id="c-phone" name="phone"></div>
                    <div class="field"><label for="c-subject">Subject</label><input id="c-subject" name="subject"></div>
                    <div class="field field-full"><label for="c-message">Message</label><textarea id="c-message" name="message" rows="5" required></textarea></div>
                </div>
                <button class="btn btn-primary" type="submit">Send message</button>
            </form>
        </div>
        <div class="reveal">
            <div class="card">
                <h3>School address</h3>
                <p class="muted"><?= e(SCHOOL_ADDRESS) ?></p>
                <h3 class="mt-2">Phone</h3>
                <p class="muted"><a href="tel:<?= e(SCHOOL_PHONE) ?>"><?= e(SCHOOL_PHONE) ?></a></p>
                <h3 class="mt-2">Email</h3>
                <p class="muted"><a href="mailto:<?= e(SCHOOL_EMAIL) ?>"><?= e(SCHOOL_EMAIL) ?></a></p>
                <h3 class="mt-2">Office hours</h3>
                <p class="muted">Monday – Friday, 7:30am – 4:00pm</p>
            </div>
            <div class="video-frame mt-2"><div class="placeholder">Map placeholder — embed Google Maps for Tunga, Minna</div></div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
