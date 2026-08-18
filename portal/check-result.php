<?php
/** Public result checker: admission number + surname + session + term. */
$pageTitle = 'Check Result';
$activeNav = 'results';
$extraCss = ['dashboard.css'];
require_once dirname(__DIR__) . '/config.php';

$sheet = null;
$error = null;
$sheetSession = $_GET['session_name'] ?? current_session_name();
$sheetTerm = $_GET['term'] ?? current_term();

if (isset($_GET['admission_no'])) {
    $admission = trim((string) $_GET['admission_no']);
    $surname = strtolower(trim((string) ($_GET['surname'] ?? '')));
    $student = Database::one("SELECT * FROM users WHERE admission_no = ? AND role = 'student'", [$admission]);

    if (!$student) {
        $error = 'No student was found with that admission number.';
    } elseif ($surname !== '' && !str_contains(strtolower($student['full_name']), $surname)) {
        $error = 'The surname does not match our records for that admission number.';
    } else {
        $candidate = ResultCalculator::sheet((int) $student['id'], $sheetSession, $sheetTerm);
        if (!$candidate['summary'] || !$candidate['summary']['published']) {
            $error = 'The result for this term has not been published yet. Please check back later.';
        } else {
            $sheet = $candidate;
        }
    }
}

$sessions = Database::all('SELECT DISTINCT session_name FROM results ORDER BY session_name DESC');
require dirname(__DIR__) . '/frontend/partials/header.php';
?>
<section class="page-hero no-print">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Check result</div>
        <h1>Check your result online</h1>
        <p>Enter your admission number to view and print your termly result sheet.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:900px">
        <div class="card no-print">
            <form id="resultCheckForm" action="<?= url('portal/check-result.php') ?>" method="get">
                <div class="form-grid">
                    <div class="field"><label for="admission_no">Admission number</label><input id="admission_no" name="admission_no" value="<?= e($_GET['admission_no'] ?? '') ?>" required></div>
                    <div class="field"><label for="surname">Surname</label><input id="surname" name="surname" value="<?= e($_GET['surname'] ?? '') ?>"></div>
                    <div class="field">
                        <label for="session_name">Session</label>
                        <select id="session_name" name="session_name">
                            <?php $options = array_column($sessions, 'session_name') ?: [current_session_name()]; ?>
                            <?php foreach ($options as $option): ?>
                                <option value="<?= e($option) ?>" <?= $option === $sheetSession ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="term">Term</label>
                        <select id="term" name="term">
                            <?php foreach (['First', 'Second', 'Third'] as $term): ?>
                                <option value="<?= $term ?>" <?= $term === $sheetTerm ? 'selected' : '' ?>><?= $term ?> Term</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Check result</button>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error mt-2 no-print"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($sheet): ?>
            <div class="mt-3">
                <?php require dirname(__DIR__) . '/backend/includes/layout/result-sheet.php'; ?>
                <div class="text-center mt-2 no-print">
                    <button class="btn btn-primary" id="printResult" type="button">Print result sheet</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="<?= url('assets/js/result-calculator.js') ?>"></script>
<?php require dirname(__DIR__) . '/frontend/partials/footer.php'; ?>
