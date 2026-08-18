<?php
/** Fee structure per class and term, plus the school bank accounts shown to parents. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_fees');

$pageTitle = 'Fee structure';
$activeMenu = 'fees';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'fee') {
        Database::run(
            'INSERT INTO fee_structure (class_id, term, session_name, amount, description)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE amount = VALUES(amount), description = VALUES(description)',
            [
                (int) $_POST['class_id'],
                $_POST['term'],
                $_POST['session_name'] ?: current_session_name(),
                (float) $_POST['amount'],
                $_POST['description'] ?: null,
            ]
        );
        AuditLogger::log('fees.update', 'class', (string) (int) $_POST['class_id'], $_POST['term'] . ' — ' . $_POST['amount']);
        flash('Fee saved.');
    }
    if (($_POST['action'] ?? '') === 'bank') {
        Database::insertRow('school_bank_accounts', [
            'bank_name'      => $_POST['bank_name'],
            'account_name'   => $_POST['account_name'],
            'account_number' => $_POST['account_number'],
        ]);
        flash('Bank account added.');
    }
    redirect('/admin/payments/fees.php');
}

$classes = classes_list();
$fees = Database::all('SELECT f.*, c.name AS class_name FROM fee_structure f JOIN school_classes c ON c.id = f.class_id ORDER BY f.session_name DESC, c.level_order, f.term');
$banks = Database::all('SELECT * FROM school_bank_accounts ORDER BY id DESC');

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Set a fee</h2></div>
    <form method="post" class="filters">
        <input type="hidden" name="action" value="fee">
        <div class="field">
            <label for="class_id">Class</label>
            <select id="class_id" name="class_id" required>
                <?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= e($class['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="term">Term</label>
            <select id="term" name="term"><option>First</option><option>Second</option><option>Third</option></select>
        </div>
        <div class="field"><label for="session_name">Session</label><input id="session_name" name="session_name" value="<?= e(current_session_name()) ?>"></div>
        <div class="field"><label for="amount">Amount (₦)</label><input id="amount" type="number" step="0.01" name="amount" required></div>
        <div class="field"><label for="description">Covers</label><input id="description" name="description" placeholder="Tuition, books, exam fee"></div>
        <button class="btn btn-primary" type="submit">Save fee</button>
    </form>

    <div class="table-wrap mt-2">
        <table class="data">
            <thead><tr><th>Class</th><th>Term</th><th>Session</th><th>Amount</th><th>Covers</th></tr></thead>
            <tbody>
            <?php foreach ($fees as $fee): ?>
                <tr><td><?= e($fee['class_name']) ?></td><td><?= e($fee['term']) ?></td><td><?= e($fee['session_name']) ?></td><td><?= money($fee['amount']) ?></td><td class="muted"><?= e($fee['description'] ?? '—') ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$fees): ?><tr><td colspan="5" class="muted">No fees set yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>School bank accounts</h2></div>
    <form method="post" class="filters">
        <input type="hidden" name="action" value="bank">
        <div class="field"><label for="bank_name">Bank</label><input id="bank_name" name="bank_name" required></div>
        <div class="field"><label for="account_name">Account name</label><input id="account_name" name="account_name" required></div>
        <div class="field"><label for="account_number">Account number</label><input id="account_number" name="account_number" required></div>
        <button class="btn btn-primary" type="submit">Add account</button>
    </form>
    <div class="table-wrap mt-2">
        <table class="data">
            <thead><tr><th>Bank</th><th>Account name</th><th>Account number</th></tr></thead>
            <tbody>
            <?php foreach ($banks as $bank): ?>
                <tr><td><?= e($bank['bank_name']) ?></td><td><?= e($bank['account_name']) ?></td><td><?= e($bank['account_number']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$banks): ?><tr><td colspan="3" class="muted">No bank accounts added yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
