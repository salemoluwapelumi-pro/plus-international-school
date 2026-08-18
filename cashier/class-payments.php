<?php
/** Class-by-class payment table: student name, class and receipt. */
require_once dirname(__DIR__) . '/config.php';
Auth::requirePermission('view_payments');

$pageTitle = 'Payments class by class';
$activeMenu = 'class-payments';

$classes = classes_list();
$classId = (int) ($_GET['class_id'] ?? 0);
$grouped = PaymentProcessor::approvedByClass($classId ?: null);

require_once dirname(__DIR__) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Filter by class</h2></div>
    <form method="get" class="filters">
        <div class="field">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id" onchange="this.form.submit()">
                <option value="0">All classes</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Show</button>
    </form>
</div>

<?php foreach ($grouped as $className => $rows): ?>
    <div class="panel mt-3">
        <div class="panel-head"><h2><?= e($className) ?></h2><span class="badge badge-green"><?= count($rows) ?> paid · <?= money(array_sum(array_column($rows, 'amount'))) ?></span></div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Student name</th><th>Class</th><th>Receipt</th><th>Amount</th><th>Term</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['student_name']) ?></td>
                        <td><?= e($row['class_name']) ?></td>
                        <td><strong><?= e($row['receipt_number'] ?: '—') ?></strong></td>
                        <td><?= money($row['amount']) ?></td>
                        <td><?= e($row['term']) ?></td>
                        <td><a class="btn btn-ghost btn-sm" target="_blank" href="<?= url('cashier/receipt.php?id=' . (int) $row['id']) ?>">Receipt</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!$grouped): ?>
    <div class="panel mt-3"><div class="empty-state"><div class="ico">🧾</div>No approved payments yet.</div></div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/backend/includes/layout/dash-footer.php'; ?>
