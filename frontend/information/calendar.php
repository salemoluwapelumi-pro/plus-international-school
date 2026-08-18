<?php
$pageTitle = 'Academic Calendar';
$activeNav = 'academics';
require __DIR__ . '/../partials/header.php';

$sessions = [];
try {
    $sessions = Database::all('SELECT * FROM academic_sessions ORDER BY name DESC, FIELD(term,"First","Second","Third")');
} catch (Throwable $e) {
    $sessions = [];
}
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Academics · Calendar</div>
        <h1>Academic calendar</h1>
        <p>Term dates, examination weeks and holidays for the current academic year.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="table-wrap reveal">
            <table class="data">
                <thead><tr><th>Session</th><th>Term</th><th>Begins</th><th>Ends</th><th>Status</th></tr></thead>
                <tbody>
                <?php if ($sessions): foreach ($sessions as $row): ?>
                    <tr>
                        <td><?= e($row['name']) ?></td>
                        <td><?= e($row['term']) ?> Term</td>
                        <td><?= pretty_date($row['starts_on']) ?></td>
                        <td><?= pretty_date($row['ends_on']) ?></td>
                        <td><?= $row['is_current'] ? '<span class="badge badge-green">Current</span>' : '<span class="badge badge-gray">Closed</span>' ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="muted">The calendar has not been published yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
