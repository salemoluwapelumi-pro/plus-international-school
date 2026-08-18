<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Home';
$activeNav = 'home';

$news = [];
try {
    $news = Database::all("SELECT * FROM announcements WHERE audience = 'public' ORDER BY id DESC LIMIT 3");
} catch (Throwable $e) {
    // The site still renders before the database has been seeded.
}

require __DIR__ . '/frontend/partials/header.php';
?>

<section class="hero">
    <div class="hero-slides">
        <div class="hero-slide active" style="background-image:url('<?= url('assets/images/hero/hero-1.jpg') ?>')"></div>
        <div class="hero-slide" style="background-image:url('<?= url('assets/images/hero/hero-2.jpg') ?>')"></div>
        <div class="hero-slide" style="background-image:url('<?= url('assets/images/campus/campus-main.jpg') ?>')"></div>
    </div>
    <div class="container hero-inner">
        <span class="hero-badge">Nursery · Primary · Secondary</span>
        <h1>Where every child is raised to be <em>confident, disciplined and globally competitive</em>.</h1>
        <p>Plus International School, Tunga, Minna, combines a rigorous Nigerian and international curriculum with modern facilities, caring teachers and a fully digital school portal for results, fees and communication.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= url('frontend/admissions/apply-online.php') ?>">Apply for admission</a>
            <a class="btn btn-outline" href="<?= url('portal/check-result.php') ?>">Check your result</a>
            <a class="btn btn-outline" href="<?= url('student/payments/pay.php') ?>">Pay school fees</a>
        </div>
        <div class="hero-stats">
            <div><strong data-count="1250" data-suffix="+">0</strong><span>Students enrolled</span></div>
            <div><strong data-count="86">0</strong><span>Qualified teachers</span></div>
            <div><strong data-count="24">0</strong><span>Years of excellence</span></div>
            <div><strong data-count="98" data-suffix="%">0</strong><span>WAEC / NECO pass rate</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title reveal">
            <span>Why Plus International</span>
            <h2>An education built on knowledge, character and excellence</h2>
            <p>Everything a modern school needs — in the classroom and online.</p>
        </div>
        <div class="grid grid-4">
            <?php
            $features = [
                ['🎓', 'Strong academics', 'Nigerian curriculum enriched with international best practice from nursery to SS3.'],
                ['🧪', 'Modern laboratories', 'Science, ICT and robotics laboratories that make learning practical.'],
                ['📊', 'Digital results', 'CA and exam scores computed instantly with grades, remarks and class positions.'],
                ['💳', 'Online fee payment', 'Pay securely with Paystack or Remita and download an official receipt.'],
                ['💬', 'Teacher–student chat', 'A safe, monitored messaging space for academic support after class.'],
                ['🚌', 'Safe transport', 'GPS-tracked buses covering Tunga, Bosso, Chanchaga and Minna central.'],
                ['🏅', 'Clubs and sports', 'Debate, coding, music, athletics and more, every single week.'],
                ['🛡', 'Safeguarding first', 'CCTV, trained matrons and a school clinic that keeps every child safe.'],
            ];
            foreach ($features as $index => [$icon, $title, $text]): ?>
                <div class="card reveal reveal-delay-<?= $index % 3 + 1 ?>">
                    <div class="icon"><?= $icon ?></div>
                    <h3><?= e($title) ?></h3>
                    <p><?= e($text) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-title reveal">
            <span>Our programmes</span>
            <h2>A complete learning journey</h2>
        </div>
        <div class="grid grid-3">
            <?php
            $programs = [
                ['Nursery School', 'Ages 2 – 5', 'Play-based early years learning that builds curiosity, speech and confidence.', 'nursery.php', 'assets/images/campus/campus-main.jpg'],
                ['Primary School', 'Primary 1 – 6', 'A firm foundation in literacy, numeracy, science, ICT and character formation.', 'primary.php', 'assets/images/facilities/library.jpg'],
                ['Secondary School', 'JSS 1 – SS 3', 'Junior and senior secondary education preparing students for WAEC, NECO and JAMB.', 'secondary.php', 'assets/images/facilities/robotics-lab.jpg'],
            ];
            foreach ($programs as [$title, $tag, $text, $page, $image]): ?>
                <div class="card program-card reveal">
                    <div class="media">
                        <span class="tag"><?= e($tag) ?></span>
                        <div class="placeholder">Image placeholder<br><?= e($image) ?></div>
                    </div>
                    <div class="body">
                        <h3><?= e($title) ?></h3>
                        <p><?= e($text) ?></p>
                        <a class="btn btn-ghost btn-sm mt-2" href="<?= url('frontend/academics/' . $page) ?>">Explore programme</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="reveal">
            <span style="color:var(--gold);font-weight:700;letter-spacing:.16em;text-transform:uppercase;font-size:.78rem">Take a tour</span>
            <h2>See our campus in Tunga, Minna</h2>
            <p class="muted">Purpose-built classrooms, laboratories, a digital library, a sports complex and a performing arts theatre — designed so learning never stops.</p>
            <ul class="checklist">
                <li>Air-conditioned, projector-equipped classrooms</li>
                <li>Science, ICT and robotics laboratories</li>
                <li>Digital library with over 8,000 titles</li>
                <li>Standard football pitch and indoor sports hall</li>
                <li>School clinic with a resident nurse</li>
            </ul>
            <a class="btn btn-blue mt-2" href="<?= url('frontend/information/facilities.php') ?>">View all facilities</a>
        </div>
        <div class="video-frame reveal">
            <div class="placeholder">Video placeholder — assets/videos/campus-tour.mp4</div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-title reveal">
            <span>Gallery</span>
            <h2>Life at Plus International School</h2>
        </div>
        <div class="gallery">
            <?php
            $gallery = ['Assembly ground', 'Science laboratory', 'Inter-house sports', 'Robotics club', 'Cultural day', 'Graduation', 'Digital library', 'Excursion'];
            foreach ($gallery as $caption): ?>
                <div class="gallery-item reveal" data-caption="<?= e($caption) ?>">
                    <div class="placeholder"><?= e($caption) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-ghost" href="<?= url('frontend/information/gallery.php') ?>">Open full gallery</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title reveal">
            <span>Online portal</span>
            <h2>Everything a parent or student needs, online</h2>
        </div>
        <div class="grid grid-3">
            <div class="card reveal">
                <div class="icon">📊</div>
                <h3>Check results instantly</h3>
                <p>Termly results with CA and exam breakdown, grades, remarks, class average and position — printable on the official school result sheet.</p>
                <a class="btn btn-ghost btn-sm mt-2" href="<?= url('portal/check-result.php') ?>">Check result</a>
            </div>
            <div class="card reveal reveal-delay-1">
                <div class="icon">💳</div>
                <h3>Pay school fees</h3>
                <p>Pay with card or bank transfer through Paystack or Remita. Your payment appears in the cashier's queue immediately and your receipt is issued on approval.</p>
                <a class="btn btn-ghost btn-sm mt-2" href="<?= url('student/payments/pay.php') ?>">Pay fees</a>
            </div>
            <div class="card reveal reveal-delay-2">
                <div class="icon">💬</div>
                <h3>Chat with teachers</h3>
                <p>Students and teachers register on the school chat system and exchange academic support messages in a monitored environment.</p>
                <a class="btn btn-ghost btn-sm mt-2" href="<?= url('chat/register.php') ?>">Join the chat</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-navy">
    <div class="container">
        <div class="section-title">
            <span style="color:var(--gold)">Testimonials</span>
            <h2>What our community says</h2>
        </div>
        <div class="text-center" style="max-width:760px;margin:0 auto">
            <blockquote data-quote style="font-size:1.15rem">“My daughter joined in Primary 3 and her confidence changed completely. The teachers know every child by name.”<br><strong style="color:var(--gold)">— Mrs. Aisha Bello, parent</strong></blockquote>
            <blockquote data-quote style="font-size:1.15rem;display:none">“Checking results online and paying fees from my phone has saved me so many trips to the school.”<br><strong style="color:var(--gold)">— Mr. Emeka Obi, parent</strong></blockquote>
            <blockquote data-quote style="font-size:1.15rem;display:none">“The robotics club is my favourite part of school. We built a line-following robot last term.”<br><strong style="color:var(--gold)">— Fatima, JSS 2</strong></blockquote>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title reveal">
            <span>News &amp; events</span>
            <h2>Latest from the school</h2>
        </div>
        <div class="grid grid-3">
            <?php if ($news): foreach ($news as $item): ?>
                <div class="card reveal">
                    <small class="muted"><?= pretty_date($item['created_at']) ?></small>
                    <h3><?= e($item['title']) ?></h3>
                    <p><?= e(mb_strimwidth(strip_tags($item['body']), 0, 150, '…')) ?></p>
                </div>
            <?php endforeach; else: ?>
                <div class="card reveal"><h3>Resumption date announced</h3><p>Second term resumes on Monday, 6 January. Please settle all outstanding fees before resumption.</p></div>
                <div class="card reveal reveal-delay-1"><h3>Inter-house sports</h3><p>Our annual inter-house sports festival holds at the school sports complex. Parents are warmly invited.</p></div>
                <div class="card reveal reveal-delay-2"><h3>Admissions now open</h3><p>Applications into Nursery, Primary and Secondary classes are open. Apply online in a few minutes.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container text-center">
        <h2>Ready to join Plus International School?</h2>
        <p class="muted">Start an online application today, or book a campus tour with our admissions office.</p>
        <div class="flex gap-1 mt-2" style="justify-content:center;flex-wrap:wrap">
            <a class="btn btn-primary" href="<?= url('frontend/admissions/apply-online.php') ?>">Apply online</a>
            <a class="btn btn-ghost" href="<?= url('frontend/public/contact.php') ?>">Book a tour</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/frontend/partials/footer.php'; ?>
