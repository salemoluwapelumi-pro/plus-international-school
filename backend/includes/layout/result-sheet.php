<?php
/** Official school result sheet. Expects $sheet from ResultCalculator::sheet(). */
$student = $sheet['student'];
$rows = $sheet['rows'];
$summary = $sheet['summary'];
?>
<div class="result-sheet">
    <div class="result-head">
        <div class="crest">PIS</div>
        <div>
            <h2><?= e(SCHOOL_NAME) ?></h2>
            <p><?= e(SCHOOL_ADDRESS) ?> · <?= e(SCHOOL_PHONE) ?></p>
            <p><strong>Termly report sheet</strong> — <?= e($sheetSession) ?> session, <?= e($sheetTerm) ?> Term</p>
        </div>
    </div>

    <div class="result-meta">
        <div><span>Student</span><strong><?= e($student['full_name']) ?></strong></div>
        <div><span>Admission no.</span><strong><?= e($student['admission_no'] ?? '—') ?></strong></div>
        <div><span>Class</span><strong><?= e($student['class_name'] ?? '—') ?></strong></div>
        <div><span>Class size</span><strong><?= e($summary['class_size'] ?? count($rows)) ?></strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left">Subject</th>
                <th>CA 1<br><small>10</small></th>
                <th>CA 2<br><small>10</small></th>
                <th>Assign.<br><small>10</small></th>
                <th>Exam<br><small>70</small></th>
                <th>Total<br><small>100</small></th>
                <th>Grade</th>
                <th>Position</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td class="subject-name"><?= e($row['subject_name']) ?></td>
                <td><?= (float) $row['ca1'] ?></td>
                <td><?= (float) $row['ca2'] ?></td>
                <td><?= (float) $row['assignment'] ?></td>
                <td><?= (float) $row['exam'] ?></td>
                <td><strong><?= (float) $row['total'] ?></strong></td>
                <td><?= e($row['grade']) ?></td>
                <td><?= ResultCalculator::ordinal($row['subject_position'] ? (int) $row['subject_position'] : null) ?></td>
                <td><?= e($row['remark']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="9">No scores have been recorded for this term.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($summary): ?>
        <div class="result-meta mt-2">
            <div><span>Subjects offered</span><strong><?= (int) $summary['subjects_count'] ?></strong></div>
            <div><span>Total score</span><strong><?= (float) $summary['total_score'] ?></strong></div>
            <div><span>Average</span><strong><?= (float) $summary['average'] ?>%</strong></div>
            <div><span>Class average</span><strong><?= (float) $summary['class_average'] ?>%</strong></div>
        </div>
        <div class="result-meta">
            <div><span>Position in class</span><strong><?= ResultCalculator::ordinal($summary['position'] ? (int) $summary['position'] : null) ?> of <?= (int) $summary['class_size'] ?></strong></div>
            <div><span>Overall grade</span><strong><?= e($summary['overall_grade']) ?></strong></div>
            <div><span>Status</span><strong><?= $summary['published'] ? 'Published' : 'Draft' ?></strong></div>
            <div><span>Computed</span><strong><?= pretty_date($summary['computed_at']) ?></strong></div>
        </div>

        <div class="remarks">
            <div><strong>Class teacher's remark:</strong> <?= e($summary['teacher_remark']) ?></div>
            <div><strong>Principal's remark:</strong> <?= e($summary['principal_remark']) ?></div>
        </div>
    <?php endif; ?>

    <div class="signature">
        <div>Class teacher</div>
        <div>Principal</div>
        <div>Parent / guardian</div>
    </div>

    <p class="text-center muted mt-2" style="font-size:.78rem">
        Grading: A1 75–100 · B2 70–74 · B3 65–69 · C4 60–64 · C5 55–59 · C6 50–54 · D7 45–49 · E8 40–44 · F9 0–39
    </p>
</div>
