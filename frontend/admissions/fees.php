<?php
$pageTitle = 'Fee Structure';
$activeNav = 'admissions';
require __DIR__ . '/../partials/header.php';

$session = current_session_name();
$fees = [];
try {
    $fees = Database::all(
        'SELECT f.*, c.name AS class_name FROM fee_structure f
         JOIN school_classes c ON c.id = f.class_id
         WHERE f.session_name = ?
         ORDER BY c.level_order, FIELD(f.term, "First", "Second", "Third")',
        [$session]
    );
} catch (Throwable $e) {
    $fees = [];
}
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Admissions · Fees</div>
        <h1>Fee structure — <?= e($session) ?> session</h1>
        <p>Fees cover tuition, examinations, library, laboratory and club activities. Transport and boarding are billed separately.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($fees): ?>
            <div class="table-wrap reveal">
                <table class="data">
                    <thead><tr><th>Class</th><th>Term</th><th>Amount</th><th>Covers</th></tr></thead>
                    <tbody>
                    <?php foreach ($fees as $fee): ?>
                        <tr>
                            <td><?= e($fee['class_name']) ?></td>
                            <td><?= e($fee['term']) ?> Term</td>
                            <td><strong><?= money($fee['amount']) ?></strong></td>
                            <td class="muted"><?= e($fee['description'] ?? 'Tuition and school activities') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">The fee schedule for this session has not been published yet. Please contact the bursary on <?= e(SCHOOL_PHONE) ?>.</div>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="<?= url('student/payments/pay.php') ?>">Pay school fees online</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
