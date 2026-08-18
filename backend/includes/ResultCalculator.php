<?php
declare(strict_types=1);

/**
 * Advanced result computation: CA + exam totals, automatic grades, remarks,
 * subject positions, class averages and overall term position.
 *
 * Score split: CA1 (10) + CA2 (10) + Assignment (10) + Exam (70) = 100.
 */
final class ResultCalculator
{
    public const MAX_CA1 = 10;
    public const MAX_CA2 = 10;
    public const MAX_ASSIGNMENT = 10;
    public const MAX_EXAM = 70;

    /** Grade bands used across the school. */
    public const BANDS = [
        ['min' => 75, 'grade' => 'A1', 'remark' => 'Excellent'],
        ['min' => 70, 'grade' => 'B2', 'remark' => 'Very Good'],
        ['min' => 65, 'grade' => 'B3', 'remark' => 'Good'],
        ['min' => 60, 'grade' => 'C4', 'remark' => 'Credit'],
        ['min' => 55, 'grade' => 'C5', 'remark' => 'Credit'],
        ['min' => 50, 'grade' => 'C6', 'remark' => 'Credit'],
        ['min' => 45, 'grade' => 'D7', 'remark' => 'Pass'],
        ['min' => 40, 'grade' => 'E8', 'remark' => 'Weak Pass'],
        ['min' => 0,  'grade' => 'F9', 'remark' => 'Fail'],
    ];

    public static function total(float $ca1, float $ca2, float $assignment, float $exam): float
    {
        return round(
            min($ca1, self::MAX_CA1)
            + min($ca2, self::MAX_CA2)
            + min($assignment, self::MAX_ASSIGNMENT)
            + min($exam, self::MAX_EXAM),
            2
        );
    }

    public static function grade(float $total): array
    {
        foreach (self::BANDS as $band) {
            if ($total >= $band['min']) {
                return ['grade' => $band['grade'], 'remark' => $band['remark']];
            }
        }
        return ['grade' => 'F9', 'remark' => 'Fail'];
    }

    /** Saves one subject score, recomputing total, grade and remark. */
    public static function saveScore(array $data, int $enteredBy): int
    {
        $total = self::total(
            (float) $data['ca1'],
            (float) $data['ca2'],
            (float) ($data['assignment'] ?? 0),
            (float) $data['exam']
        );
        $graded = self::grade($total);

        Database::run(
            'INSERT INTO results
                (student_id, class_id, subject_id, session_name, term, ca1, ca2, assignment, exam, total, grade, remark, entered_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                ca1 = VALUES(ca1), ca2 = VALUES(ca2), assignment = VALUES(assignment), exam = VALUES(exam),
                total = VALUES(total), grade = VALUES(grade), remark = VALUES(remark), entered_by = VALUES(entered_by)',
            [
                $data['student_id'], $data['class_id'], $data['subject_id'], $data['session_name'], $data['term'],
                $data['ca1'], $data['ca2'], $data['assignment'] ?? 0, $data['exam'],
                $total, $graded['grade'], $graded['remark'], $enteredBy,
            ]
        );
        AuditLogger::log('result.save', 'student', (string) $data['student_id'], $data['session_name'] . ' ' . $data['term']);

        self::computeClass((int) $data['class_id'], $data['session_name'], $data['term']);
        return (int) $total;
    }

    /** Recomputes summaries, positions and class averages for an entire class. */
    public static function computeClass(int $classId, string $session, string $term): array
    {
        $students = Database::all(
            'SELECT DISTINCT student_id FROM results WHERE class_id = ? AND session_name = ? AND term = ?',
            [$classId, $session, $term]
        );

        $summaries = [];
        foreach ($students as $row) {
            $studentId = (int) $row['student_id'];
            $agg = Database::one(
                'SELECT COUNT(*) AS subjects, COALESCE(SUM(total),0) AS total_score
                 FROM results WHERE student_id = ? AND session_name = ? AND term = ?',
                [$studentId, $session, $term]
            );
            $subjects = (int) $agg['subjects'];
            $totalScore = (float) $agg['total_score'];
            $average = $subjects ? round($totalScore / $subjects, 2) : 0.0;
            $summaries[] = [
                'student_id' => $studentId,
                'subjects'   => $subjects,
                'total'      => $totalScore,
                'average'    => $average,
            ];
        }

        $classSize = count($summaries);
        $classAverage = $classSize
            ? round(array_sum(array_column($summaries, 'average')) / $classSize, 2)
            : 0.0;

        usort($summaries, static fn ($a, $b) => $b['average'] <=> $a['average']);

        $position = 0;
        $previousAverage = null;
        foreach ($summaries as $index => $summary) {
            if ($previousAverage === null || $summary['average'] < $previousAverage) {
                $position = $index + 1;
                $previousAverage = $summary['average'];
            }
            $graded = self::grade($summary['average']);
            Database::run(
                'INSERT INTO result_summaries
                    (student_id, class_id, session_name, term, subjects_count, total_score, average, class_average,
                     position, class_size, overall_grade, teacher_remark, principal_remark)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                    class_id = VALUES(class_id), subjects_count = VALUES(subjects_count), total_score = VALUES(total_score),
                    average = VALUES(average), class_average = VALUES(class_average), position = VALUES(position),
                    class_size = VALUES(class_size), overall_grade = VALUES(overall_grade),
                    teacher_remark = VALUES(teacher_remark), principal_remark = VALUES(principal_remark),
                    computed_at = NOW()',
                [
                    $summary['student_id'], $classId, $session, $term, $summary['subjects'], $summary['total'],
                    $summary['average'], $classAverage, $position, $classSize, $graded['grade'],
                    self::teacherRemark($summary['average']), self::principalRemark($summary['average']),
                ]
            );
        }

        self::computeSubjectPositions($classId, $session, $term);
        return $summaries;
    }

    private static function computeSubjectPositions(int $classId, string $session, string $term): void
    {
        $subjects = Database::all(
            'SELECT DISTINCT subject_id FROM results WHERE class_id = ? AND session_name = ? AND term = ?',
            [$classId, $session, $term]
        );
        foreach ($subjects as $subject) {
            $rows = Database::all(
                'SELECT id, total FROM results
                 WHERE class_id = ? AND session_name = ? AND term = ? AND subject_id = ?
                 ORDER BY total DESC',
                [$classId, $session, $term, $subject['subject_id']]
            );
            $position = 0;
            $previous = null;
            foreach ($rows as $index => $row) {
                if ($previous === null || (float) $row['total'] < $previous) {
                    $position = $index + 1;
                    $previous = (float) $row['total'];
                }
                Database::run('UPDATE results SET subject_position = ? WHERE id = ?', [$position, $row['id']]);
            }
        }
    }

    public static function teacherRemark(float $average): string
    {
        return match (true) {
            $average >= 75 => 'An outstanding result. Keep it up!',
            $average >= 60 => 'A very good performance. Aim even higher.',
            $average >= 50 => 'A fair result; more effort will improve it.',
            $average >= 40 => 'Below expectation. Serious work is needed.',
            default        => 'Poor performance. Requires urgent attention.',
        };
    }

    public static function principalRemark(float $average): string
    {
        return match (true) {
            $average >= 75 => 'Excellent. Promoted with distinction.',
            $average >= 50 => 'Satisfactory. Promoted to the next class.',
            $average >= 40 => 'Promoted on trial. Improvement expected.',
            default        => 'Result requires review with parents.',
        };
    }

    public static function publish(int $classId, string $session, string $term, bool $published = true): void
    {
        Database::run(
            'UPDATE results SET published = ? WHERE class_id = ? AND session_name = ? AND term = ?',
            [$published ? 1 : 0, $classId, $session, $term]
        );
        Database::run(
            'UPDATE result_summaries SET published = ? WHERE class_id = ? AND session_name = ? AND term = ?',
            [$published ? 1 : 0, $classId, $session, $term]
        );
        AuditLogger::log($published ? 'result.publish' : 'result.unpublish', 'class', (string) $classId, "$session $term");
    }

    /** Everything needed to render or print a student's result sheet. */
    public static function sheet(int $studentId, string $session, string $term): array
    {
        $student = Database::one(
            'SELECT u.*, c.name AS class_name FROM users u
             LEFT JOIN school_classes c ON c.id = u.class_id WHERE u.id = ?',
            [$studentId]
        );
        $rows = Database::all(
            'SELECT r.*, s.name AS subject_name FROM results r
             JOIN subjects s ON s.id = r.subject_id
             WHERE r.student_id = ? AND r.session_name = ? AND r.term = ?
             ORDER BY s.name',
            [$studentId, $session, $term]
        );
        $summary = Database::one(
            'SELECT * FROM result_summaries WHERE student_id = ? AND session_name = ? AND term = ?',
            [$studentId, $session, $term]
        );
        return ['student' => $student, 'rows' => $rows, 'summary' => $summary];
    }

    public static function ordinal(?int $number): string
    {
        if ($number === null) {
            return '—';
        }
        $suffix = 'th';
        if (!in_array($number % 100, [11, 12, 13], true)) {
            $suffix = match ($number % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
        }
        return $number . $suffix;
    }
}
