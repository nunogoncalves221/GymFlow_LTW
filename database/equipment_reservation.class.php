<?php
declare(strict_types=1);

class EquipmentReservation {
    public int $id;
    public int $user_id;
    public int $equipment_id;
    public string $equipment_name;
    public string $date;
    public string $start_time;
    public string $end_time;
    public string $status;

    public function __construct(
        int $id, int $user_id, int $equipment_id, string $equipment_name,
        string $date, string $start_time, string $end_time, string $status
    ) {
        $this->id             = $id;
        $this->user_id        = $user_id;
        $this->equipment_id   = $equipment_id;
        $this->equipment_name = $equipment_name;
        $this->date           = $date;
        $this->start_time     = $start_time;
        $this->end_time       = $end_time;
        $this->status         = $status;
    }

    private static function fromRow(array $row): self {
        return new self(
            (int)$row['id'],
            (int)$row['user_id'],
            (int)$row['equipment_id'],
            $row['equipment_name'] ?? '',
            $row['date'],
            $row['start_time'],
            $row['end_time'],
            $row['status']
        );
    }

    static function countOverlapping(PDO $db, int $equipmentId, string $date, string $startTime, string $endTime, ?int $excludeId = null): int {
        $sql    = "SELECT COUNT(*) FROM equipment_reservations
                   WHERE equipment_id = ? AND date = ? AND status = 'active'
                   AND start_time < ? AND end_time > ?";
        $params = [$equipmentId, $date, $endTime, $startTime];
        if ($excludeId !== null) {
            $sql    .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    static function create(PDO $db, int $userId, int $equipmentId, string $date, string $startTime, string $endTime): string {
        if ($date < date('Y-m-d')) {
            return 'error:You can only reserve equipment for today or future dates.';
        }

        if ($date > date('Y-m-d', strtotime('+7 days'))) {
            return 'error:You can only reserve equipment up to one week in advance.';
        }

        if ($startTime >= $endTime) {
            return 'error:End time must be after start time.';
        }

        [$sh, $sm] = explode(':', $startTime);
        [$eh, $em] = explode(':', $endTime);
        $durationMin = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
        if ($durationMin < 10) {
            return 'error:Minimum reservation duration is 10 minutes.';
        }
        if ($durationMin > 120) {
            return 'error:Maximum reservation duration is 2 hours.';
        }

        $stmt = $db->prepare('SELECT quantity FROM equipment WHERE id = ?');
        $stmt->execute([$equipmentId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'error:Equipment not found.';
        }

        $overlapping = self::countOverlapping($db, $equipmentId, $date, $startTime, $endTime);
        if ($overlapping >= (int)$row['quantity']) {
            return 'error:No units available for that time slot. Try a different time.';
        }

        // Prevent double-booking the same equipment by the same user at the same time
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM equipment_reservations
             WHERE user_id = ? AND equipment_id = ? AND date = ? AND status = 'active'
             AND start_time < ? AND end_time > ?"
        );
        $stmt->execute([$userId, $equipmentId, $date, $endTime, $startTime]);
        if ((int)$stmt->fetchColumn() > 0) {
            return 'error:You already have a reservation for this equipment at that time.';
        }

        $stmt = $db->prepare(
            'INSERT INTO equipment_reservations (user_id, equipment_id, date, start_time, end_time)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $equipmentId, $date, $startTime, $endTime]);
        return 'success:Reservation confirmed!';
    }

    static function cancel(PDO $db, int $id, int $userId): string {
        $stmt = $db->prepare('SELECT * FROM equipment_reservations WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'error:Reservation not found.';
        }
        if ($row['status'] !== 'active') {
            return 'error:This reservation is already cancelled.';
        }
        $stmt = $db->prepare("UPDATE equipment_reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$id]);
        return 'success:Reservation cancelled.';
    }

    static function update(PDO $db, int $id, int $userId, string $date, string $startTime, string $endTime): string {
        $stmt = $db->prepare("SELECT * FROM equipment_reservations WHERE id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'error:Reservation not found.';
        }

        if ($date < date('Y-m-d')) {
            return 'error:You can only reserve equipment for today or future dates.';
        }
        if ($date > date('Y-m-d', strtotime('+7 days'))) {
            return 'error:You can only reserve equipment up to one week in advance.';
        }
        if ($startTime >= $endTime) {
            return 'error:End time must be after start time.';
        }
        [$sh, $sm] = explode(':', $startTime);
        [$eh, $em] = explode(':', $endTime);
        $durationMin = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
        if ($durationMin < 10) {
            return 'error:Minimum reservation duration is 10 minutes.';
        }
        if ($durationMin > 120) {
            return 'error:Maximum reservation duration is 2 hours.';
        }

        $stmt = $db->prepare('SELECT quantity FROM equipment WHERE id = ?');
        $stmt->execute([$row['equipment_id']]);
        $quantity = (int)$stmt->fetchColumn();

        $overlapping = self::countOverlapping($db, (int)$row['equipment_id'], $date, $startTime, $endTime, $id);
        if ($overlapping >= $quantity) {
            return 'error:No units available for that time slot. Try a different time.';
        }

        $stmt = $db->prepare(
            'UPDATE equipment_reservations SET date = ?, start_time = ?, end_time = ? WHERE id = ?'
        );
        $stmt->execute([$date, $startTime, $endTime, $id]);
        return 'success:Reservation updated.';
    }

    static function getUpcomingByUser(PDO $db, int $userId): array {
        $stmt = $db->prepare(
            "SELECT r.*, e.name AS equipment_name
             FROM equipment_reservations r
             JOIN equipment e ON e.id = r.equipment_id
             WHERE r.user_id = ? AND r.status = 'active'
             AND (r.date > date('now') OR (r.date = date('now') AND r.end_time > time('now')))
             ORDER BY r.date, r.start_time"
        );
        $stmt->execute([$userId]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }
}
