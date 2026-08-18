<?php
require_once dirname(__DIR__) . '/config.php';
$user = Auth::requireRole('student');

$pageTitle = 'Welcome, ' . explode(' ', $user['full_name'])[0];
$pageSubtitle = 'Your results, timetable, assignments and fees';
$activeMenu = 'dashboard';

$sessionName = current_session_name();
$term = current_term();
$summary = Database::one(
    'SELECT * FROM result_summaries WHERE student_id = ? AND session_name = ? AND term = ?',
    [$user['id'], $sessionName, $term]
);
$paid = (float) Database::value(
    "SELECT COALESCE(SUM(amount),0) FROM payment_transactions WHERE student_id = ? AND status = 'approved' AND term = ? AND session_name = ?",
    [$user['id'], $term, $sessionName]
);
$expected = $user['class_id'] ? PaymentProcessor::expectedFee((int) $user['class_id'], $term, $sessionName) : 0.0;
$assignments = $user['class_id'] ? Database::all(
    'SELECT a.*, s.name AS subject_name FROM assignments a JOIN subjects s ON s.id = a.subject_id
     WHERE a.class_id = ? ORDER BY a.id DESC LIMIT 5',
    [$user['class_id']]
) : [];
$today = date('l');
$lessons = $user['class_id'] && in_array($today, TimetableManager::DAYS, true)
    ? array_values(array_filter(TimetableManager::forClass((int) $user['class_id'], $sessionName, $term), static fn ($slot) => $slot['day_of_week'] === $today))
    : [];

require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat purple"><span class="ico">📊</span><div><strong><?= $summary && $summary['published'] ? (float) $summary['average'] . '%' : '—' ?></strong><small>Term average</small></div></div>
    <div class="stat gold"><span class="ico">🏅</span><div><strong><?= $summary && $summary['published'] ? ResultCalculator::ordinal((int) $summary['position']) : '—' ?></strong><small>Position in class<?= $summary ? ' of ' . (int) $summary['class_size'] : '' ?></small></div></div>
    <div class="stat green"><span class="ico">💳</span><div><strong><?= money($paid) ?></strong><small>Fees paid this term</small></div></div>
    <div class="stat blue"><span class="ico">🧮</span><div><strong><?= money(max(0, $expected - $paid)) ?></strong><small>Outstanding balance</small></div></div>
</div>

<div class="grid grid-2 mt-3">
    <div class="panel">
        <div class="panel-head"><h2>Today’s lessons — <?= e($today) ?></h2><a class="btn btn-ghost btn-sm" href="<?= url('student/academics/timetable.php') ?>">Full timetable</a></div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Period</th><th>Time</th><th>Subject</th><th>Teacher</th></tr></thead>
                <tbody>
                <?php foreach ($lessons as $lesson): ?>
                    <tr><td><?= (int) $lesson['period'] ?></td><td><?= e(substr($lesson['starts_at'], 0, 5)) ?>–<?= e(substr($lesson['ends_at'], 0, 5)) ?></td><td><?= e($lesson['subject_name']) ?></td><td><?= e($lesson['teacher_name'] ?? '—') ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$lessons): ?><tr><td colspan="4" class="muted">No lessons scheduled for today.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Latest assignments</h2><a class="btn btn-ghost btn-sm" href="<?= url('student/academics/assignments.php') ?>">See all</a></div>
        <ul class="plain-list">
            <?php foreach ($assignments as $assignment): ?>
                <li><strong><?= e($assignment['title']) ?></strong><br><small class="muted"><?= e($assignment['subject_name']) ?> · due <?= pretty_date($assignment['due_date']) ?></small></li>
            <?php endforeach; ?>
            <?php if (!$assignments): ?><li class="muted">No assignments have been set yet.</li><?php endif; ?>
        </ul>
        <div class="grid mt-2">
            <a class="btn btn-primary" href="<?= url('student/academics/results.php') ?>">Check my result</a>
            <a class="btn btn-gold" href="<?= url('student/payments/pay.php') ?>">Pay school fees</a>
        </div>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
