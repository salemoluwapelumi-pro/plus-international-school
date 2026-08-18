<?php
/** School fee payment: Paystack, Remita, bank transfer or cash at the bursary. */
require_once dirname(__DIR__, 2) . '/config.php';
$user = Auth::requireRole('student', 'parent');

$pageTitle = 'Pay school fees';
$pageSubtitle = 'Pay online with Paystack or Remita, or upload proof of a bank transfer';
$activeMenu = 'pay';

$classes = classes_list();
$banks = Database::all('SELECT * FROM school_bank_accounts ORDER BY id DESC');
$term = current_term();
$classId = (int) ($user['class_id'] ?? 0);
$expected = $classId ? PaymentProcessor::expectedFee($classId, $term, current_session_name()) : 0.0;

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="grid grid-2">
    <div class="panel">
        <div class="panel-head"><h2>Fee payment</h2><span class="badge badge-purple"><?= e($term) ?> Term</span></div>

        <form id="feePaymentForm" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field field-full"><label for="student_name">Student name</label><input id="student_name" name="student_name" value="<?= e($user['full_name']) ?>" required></div>
                <div class="field"><label for="admission_no">Admission number</label><input id="admission_no" name="admission_no" value="<?= e($user['admission_no'] ?? '') ?>"></div>
                <div class="field"><label for="email">Email for the receipt</label><input id="email" type="email" name="email" value="<?= e($user['email']) ?>" required></div>
                <div class="field">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" required>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>><?= e($class['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="term">Term</label>
                    <select id="term" name="term">
                        <?php foreach (['First', 'Second', 'Third'] as $option): ?>
                            <option value="<?= $option ?>" <?= $term === $option ? 'selected' : '' ?>><?= $option ?> Term</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="student_status">Student status</label>
                    <select id="student_status" name="student_status">
                        <option value="new" <?= ($user['student_status'] ?? '') === 'new' ? 'selected' : '' ?>>New student</option>
                        <option value="returning" <?= ($user['student_status'] ?? '') === 'returning' ? 'selected' : '' ?>>Returning student</option>
                    </select>
                </div>
                <div class="field"><label for="amount">Amount to pay (₦)</label><input id="amount" type="number" step="0.01" name="amount" value="<?= $expected ?: '' ?>" required></div>
            </div>

            <p class="muted" id="expectedFeeNote"><?= $expected ? 'Fee for this class and term: ' . money($expected) : 'Select your class and term to see the fee.' ?></p>

            <fieldset class="mt-2">
                <legend>How would you like to pay?</legend>
                <label class="check"><input type="radio" name="channel" value="paystack" checked> <span>Paystack — card, transfer or USSD</span></label>
                <label class="check"><input type="radio" name="channel" value="remita"> <span>Remita — pay at any bank with an RRR</span></label>
                <label class="check"><input type="radio" name="channel" value="bank-transfer"> <span>Bank transfer — upload proof of payment</span></label>
                <label class="check"><input type="radio" name="channel" value="cash"> <span>Cash at the school bursary</span></label>
            </fieldset>

            <div data-channel="bank-transfer" style="display:none" class="mt-2">
                <div class="field"><label for="proof">Proof of payment</label><input id="proof" type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png,.webp"></div>
            </div>
            <div id="remitaResult" class="alert alert-info mt-2" style="display:none"></div>

            <button class="btn btn-primary btn-block mt-2" type="submit">Pay school fees</button>
            <p class="muted mt-1">Your payment goes straight to the cashier for approval, and your receipt is issued as soon as it is approved.</p>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>School bank accounts</h2></div>
        <?php foreach ($banks as $bank): ?>
            <div class="bank-card">
                <strong><?= e($bank['bank_name']) ?></strong>
                <div><?= e($bank['account_name']) ?></div>
                <div class="account-number"><?= e($bank['account_number']) ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (!$banks): ?><div class="empty-state">Bank details will appear here once the bursary adds them.</div><?php endif; ?>

        <div class="alert alert-info mt-2">
            After a bank transfer, upload your teller or transfer receipt so the cashier can confirm and issue your official receipt.
        </div>
    </div>
</div>

<script>window.PAYSTACK_PUBLIC_KEY = <?= json_encode(PAYSTACK_PUBLIC_KEY) ?>;</script>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="<?= url('assets/js/payment-system.js') ?>"></script>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
