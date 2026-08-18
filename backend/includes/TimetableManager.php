<?php
declare(strict_types=1);

/**
 * Weekly timetable: slots are stored per class / day / period and rendered as
 * a Monday-to-Friday grid that repeats week after week for the session.
 */
final class TimetableManager
{
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    /** Default period start/end times used when creating slots. */
    public const PERIODS = [
        1 => ['starts_at' => '08:00', 'ends_at' => '08:40'],
        2 => ['starts_at' => '08:40', 'ends_at' => '09:20'],
        3 => ['starts_at' => '09:30', 'ends_at' => '10:10'],
        4 => ['starts_at' => '10:10', 'ends_at' => '10:50'],
        5 => ['starts_at' => '11:20', 'ends_at' => '12:00'],
        6 => ['starts_at' => '12:00', 'ends_at' => '12:40'],
        7 => ['starts_at' => '13:00', 'ends_at' => '13:40'],
        8 => ['starts_at' => '13:40', 'ends_at' => '14:20'],
    ];

    public static function save(array $data): void
    {
        $defaults = self::PERIODS[(int) $data['period']] ?? ['starts_at' => '08:00', 'ends_at' => '08:40'];
        Database::run(
            'INSERT INTO timetable_slots
                (class_id, subject_id, teacher_id, day_of_week, period, starts_at, ends_at, room, session_name, term)
             VALUES (?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                subject_id = VALUES(subject_id), teacher_id = VALUES(teacher_id),
                starts_at = VALUES(starts_at), ends_at = VALUES(ends_at), room = VALUES(room)',
            [
                $data['class_id'], $data['subject_id'], $data['teacher_id'] ?: null, $data['day_of_week'],
                $data['period'], $data['starts_at'] ?: $defaults['starts_at'], $data['ends_at'] ?: $defaults['ends_at'],
                $data['room'] ?? null, $data['session_name'], $data['term'],
            ]
        );
        AuditLogger::log('timetable.save', 'class', (string) $data['class_id'], $data['day_of_week'] . ' P' . $data['period']);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM timetable_slots WHERE id = ?', [$id]);
        AuditLogger::log('timetable.delete', 'slot', (string) $id);
    }

    /** Every slot for one class, ordered day by day. */
    public static function forClass(int $classId, string $session, string $term): array
    {
        return Database::all(
            'SELECT t.*, s.name AS subject_name, c.name AS class_name, u.full_name AS teacher_name
             FROM timetable_slots t
             JOIN subjects s ON s.id = t.subject_id
             JOIN school_classes c ON c.id = t.class_id
             LEFT JOIN users u ON u.id = t.teacher_id
             WHERE t.class_id = ? AND t.session_name = ? AND t.term = ?
             ORDER BY FIELD(t.day_of_week,"Monday","Tuesday","Wednesday","Thursday","Friday"), t.period',
            [$classId, $session, $term]
        );
    }

    /** Every class for a single day — the admin's day-by-day view. */
    public static function forDay(string $day, string $session, string $term): array
    {
        $rows = Database::all(
            'SELECT t.*, s.name AS subject_name, c.name AS class_name, c.level_order,
                    u.full_name AS teacher_name
             FROM timetable_slots t
             JOIN subjects s ON s.id = t.subject_id
             JOIN school_classes c ON c.id = t.class_id
             LEFT JOIN users u ON u.id = t.teacher_id
             WHERE t.day_of_week = ? AND t.session_name = ? AND t.term = ?
             ORDER BY c.level_order, c.name, t.period',
            [$day, $session, $term]
        );
        $byClass = [];
        foreach ($rows as $row) {
            $byClass[$row['class_name']][] = $row;
        }
        return $byClass;
    }

    public static function forTeacher(int $teacherId, string $session, string $term): array
    {
        return Database::all(
            'SELECT t.*, s.name AS subject_name, c.name AS class_name
             FROM timetable_slots t
             JOIN subjects s ON s.id = t.subject_id
             JOIN school_classes c ON c.id = t.class_id
             WHERE t.teacher_id = ? AND t.session_name = ? AND t.term = ?
             ORDER BY FIELD(t.day_of_week,"Monday","Tuesday","Wednesday","Thursday","Friday"), t.period',
            [$teacherId, $session, $term]
        );
    }
}
