<?php
declare(strict_types=1);

/**
 * School fees payment pipeline.
 *
 * submitted -> verified -> approved   (the cashier verifies and approves;
 * the admin only oversees and never has to approve). Rejected payments keep
 * their history for auditing.
 */
final class PaymentProcessor
{
    public static function reference(): string
    {
        return 'PIS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public static function receiptNumber(): string
    {
        $count = (int) Database::value('SELECT COUNT(*) FROM payment_transactions WHERE receipt_number IS NOT NULL');
        return sprintf('PIS/RCT/%s/%05d', date('Y'), $count + 1);
    }

    public static function expectedFee(int $classId, string $term, string $session): float
    {
        return (float) (Database::value(
            'SELECT amount FROM fee_structure WHERE class_id = ? AND term = ? AND session_name = ?',
            [$classId, $term, $session]
        ) ?? 0);
    }

    /** Creates a payment record submitted by a student or parent. */
    public static function submit(array $data): array
    {
        $expected = self::expectedFee((int) $data['class_id'], $data['term'], $data['session_name']);
        $reference = self::reference();

        $id = Database::insert(
            'INSERT INTO payment_transactions
                (reference, student_id, payer_id, student_name, class_id, term, session_name, amount,
                 amount_expected, channel, gateway_ref, status, student_status, proof_path, paid_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $reference,
                $data['student_id'],
                $data['payer_id'] ?? null,
                $data['student_name'],
                $data['class_id'],
                $data['term'],
                $data['session_name'],
                $data['amount'],
                $expected,
                $data['channel'],
                $data['gateway_ref'] ?? null,
                $data['status'] ?? 'submitted',
                $data['student_status'] ?? null,
                $data['proof_path'] ?? null,
                $data['paid_at'] ?? date('Y-m-d H:i:s'),
            ]
        );

        AuditLogger::log('payment.submit', 'payment', $reference, money($data['amount']));
        self::notifyCashiers($data['student_name'], (float) $data['amount']);

        return ['id' => $id, 'reference' => $reference, 'amount_expected' => $expected];
    }

    /** Marks a payment as verified (seen and checked by a cashier). */
    public static function verify(int $paymentId, int $cashierId): void
    {
        Database::run(
            "UPDATE payment_transactions SET status = 'verified', verifier_id = ? WHERE id = ? AND status = 'submitted'",
            [$cashierId, $paymentId]
        );
        AuditLogger::log('payment.verify', 'payment', (string) $paymentId);
    }

    /** Cashier approval issues the receipt number — no admin approval needed. */
    public static function approve(int $paymentId, int $cashierId): string
    {
        $receipt = self::receiptNumber();
        Database::run(
            "UPDATE payment_transactions
             SET status = 'approved', approver_id = ?, verifier_id = COALESCE(verifier_id, ?),
                 receipt_number = COALESCE(receipt_number, ?), approved_at = NOW()
             WHERE id = ? AND status IN ('submitted','verified')",
            [$cashierId, $cashierId, $receipt, $paymentId]
        );
        AuditLogger::log('payment.approve', 'payment', (string) $paymentId, $receipt);
        return $receipt;
    }

    public static function reject(int $paymentId, int $cashierId, string $note): void
    {
        Database::run(
            "UPDATE payment_transactions SET status = 'rejected', verifier_id = ?, rejection_note = ? WHERE id = ?",
            [$cashierId, $note, $paymentId]
        );
        AuditLogger::log('payment.reject', 'payment', (string) $paymentId, $note);
    }

    /** Payments waiting for the cashier — shown the moment a student pays. */
    public static function awaitingApproval(): array
    {
        return Database::all(
            "SELECT p.*, c.name AS class_name FROM payment_transactions p
             JOIN school_classes c ON c.id = p.class_id
             WHERE p.status IN ('submitted','verified')
             ORDER BY p.created_at DESC"
        );
    }

    /** Approved payments grouped class by class: name, class, receipt. */
    public static function approvedByClass(?int $classId = null): array
    {
        $sql = "SELECT p.*, c.name AS class_name FROM payment_transactions p
                JOIN school_classes c ON c.id = p.class_id
                WHERE p.status = 'approved'";
        $params = [];
        if ($classId) {
            $sql .= ' AND p.class_id = ?';
            $params[] = $classId;
        }
        $sql .= ' ORDER BY c.level_order, c.name, p.student_name';

        $grouped = [];
        foreach (Database::all($sql, $params) as $row) {
            $grouped[$row['class_name']][] = $row;
        }
        return $grouped;
    }

    public static function totals(): array
    {
        return Database::one(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'approved' THEN amount END), 0) AS approved_total,
                COALESCE(SUM(CASE WHEN status IN ('submitted','verified') THEN amount END), 0) AS pending_total,
                COUNT(CASE WHEN status IN ('submitted','verified') THEN 1 END) AS pending_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) AS approved_count
             FROM payment_transactions"
        ) ?? [];
    }

    /** Server-side verification of a Paystack transaction. */
    public static function verifyPaystack(string $reference): array
    {
        if (PAYSTACK_SECRET_KEY === '') {
            return ['status' => false, 'message' => 'Paystack secret key not configured'];
        }
        $ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : ['status' => false, 'message' => 'Invalid gateway response'];
    }

    private static function notifyCashiers(string $studentName, float $amount): void
    {
        $cashiers = Database::all("SELECT id FROM users WHERE role IN ('cashier','superadmin') AND status = 'active'");
        foreach ($cashiers as $cashier) {
            NotificationSystem::send(
                (int) $cashier['id'],
                'New fee payment received',
                sprintf('%s paid %s and is awaiting approval.', $studentName, money($amount))
            );
        }
    }
}
