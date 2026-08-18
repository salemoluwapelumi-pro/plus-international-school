<?php
require_once dirname(__DIR__) . '/config.php';
$user = Auth::requireRole('parent');

$pageTitle = 'Parent dashboard';
$pageSubtitle = 'Follow your children’s progress and fees';
$activeMenu = 'dashboard';

$children = Database::all(
    'SELECT u.*, c.name AS class_name FROM parent_students ps
     JOIN users u ON u.id = ps.student_id
     LEFT JOIN school_classes c ON c.id = u.class_id
     WHERE ps.parent_id = ? ORDER BY u.full_name',
    [$user['id']]
);
$sessionName = current_session_name();
$term = current_term();

require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="stats">
    <div class="stat purple"><span class="ico">👨‍👩‍👧</span><div><strong><?= count($children) ?></strong><small>Children enrolled</small></div></div>
    <div class="stat blue"><span class="ico">📅</span><div><strong><?= e($term) ?> Term</strong><small><?= e($sessionName) ?></small></div></div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>My children</h2></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Name</th><th>Admission no.</th><th>Class</th><th>Term average</th><th>Fees paid</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($children as $child):
                $summary = Database::one('SELECT * FROM result_summaries WHERE student_id = ? AND session_name = ? AND term = ?', [$child['id'], $sessionName, $term]);
                $paid = (float) Database::value("SELECT COALESCE(SUM(amount),0) FROM payment_transactions WHERE student_id = ? AND status = 'approved' AND term = ? AND session_name = ?", [$child['id'], $term, $sessionName]);
            ?>
                <tr>
                    <td><?= e($child['full_name']) ?></td>
                    <td><?= e($child['admission_no'] ?? '—') ?></td>
                    <td><?= e($child['class_name'] ?? '—') ?></td>
                    <td><?= $summary && $summary['published'] ? (float) $summary['average'] . '%' : 'Not published' ?></td>
                    <td><?= money($paid) ?></td>
                    <td class="actions">
                        <a class="btn btn-ghost btn-sm" href="<?= url('parent/children/results.php?student_id=' . (int) $child['id']) ?>">Result</a>
                        <a class="btn btn-ghost btn-sm" href="<?= url('parent/children/payments.php?student_id=' . (int) $child['id']) ?>">Payments</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$children): ?><tr><td colspan="6" class="muted">No children are linked to your account yet. Please contact the school office.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
