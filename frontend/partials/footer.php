<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="brand" href="<?= url('index.php') ?>">
                    <span class="brand-mark">PIS</span>
                    <span class="brand-text"><strong><?= e(SCHOOL_NAME) ?></strong><small><?= e(SCHOOL_MOTTO) ?></small></span>
                </a>
                <p class="mt-2">A nursery, primary and secondary school in Tunga, Minna, raising confident, disciplined and globally competitive learners.</p>
                <div class="socials">
                    <a href="#" aria-label="Facebook">f</a>
                    <a href="#" aria-label="X">𝕏</a>
                    <a href="#" aria-label="Instagram">◎</a>
                    <a href="#" aria-label="YouTube">▶</a>
                </div>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= url('frontend/public/about.php') ?>">About the school</a></li>
                    <li><a href="<?= url('frontend/admissions/admission.php') ?>">Admissions</a></li>
                    <li><a href="<?= url('frontend/information/facilities.php') ?>">Facilities</a></li>
                    <li><a href="<?= url('frontend/information/news.php') ?>">News &amp; events</a></li>
                    <li><a href="<?= url('frontend/public/contact.php') ?>">Contact us</a></li>
                </ul>
            </div>
            <div>
                <h4>Portals</h4>
                <ul>
                    <li><a href="<?= url('portal/login.php') ?>">Portal login</a></li>
                    <li><a href="<?= url('portal/register.php') ?>">Student registration</a></li>
                    <li><a href="<?= url('portal/check-result.php') ?>">Check result</a></li>
                    <li><a href="<?= url('student/payments/pay.php') ?>">Pay school fees</a></li>
                    <li><a href="<?= url('chat/login.php') ?>">Chat system</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <li><?= e(SCHOOL_ADDRESS) ?></li>
                    <li><a href="tel:<?= e(SCHOOL_PHONE) ?>"><?= e(SCHOOL_PHONE) ?></a></li>
                    <li><a href="mailto:<?= e(SCHOOL_EMAIL) ?>"><?= e(SCHOOL_EMAIL) ?></a></li>
                    <li>Mon – Fri · 7:30am – 4:00pm</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <?= e(SCHOOL_NAME) ?>. All rights reserved.</span>
            <span>Knowledge · Character · Excellence</span>
        </div>
    </div>
</footer>

<div class="modal-backdrop" id="galleryModal">
    <div class="modal">
        <div class="modal-head">
            <h3 id="galleryModalTitle">Gallery</h3>
            <button class="modal-close" data-modal-close type="button">&times;</button>
        </div>
        <div class="modal-body" id="galleryModalBody"></div>
    </div>
</div>

<script src="<?= url('assets/js/main.js') ?>"></script>
<script src="<?= url('assets/js/animations.js') ?>"></script>
</body>
</html>
