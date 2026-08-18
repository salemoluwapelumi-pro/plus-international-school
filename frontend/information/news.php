<?php
$pageTitle = 'News & Events';
$activeNav = 'info';
require __DIR__ . '/../partials/header.php';

$news = [];
try {
    $news = Database::all("SELECT * FROM announcements WHERE audience = 'public' ORDER BY id DESC LIMIT 30");
} catch (Throwable $e) {
    $news = [];
}
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · School life · News</div>
        <h1>News &amp; announcements</h1>
        <p>Term dates, events and updates published by the school administration.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($news): ?>
            <div class="grid grid-3">
                <?php foreach ($news as $item): ?>
                    <div class="card reveal">
                        <small class="muted"><?= pretty_date($item['created_at']) ?></small>
                        <h3><?= e($item['title']) ?></h3>
                        <p><?= nl2br(e($item['body'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No announcements have been published yet. Please check back soon.</div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
