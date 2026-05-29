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

    // Sweep-line analysis: finds available windows within [startTime, endTime]
    // and returns a descriptive error string.
    private static function conflictMessage(PDO $db, int $equipmentId, string $date, string $startTime, string $endTime, int $quantity, ?int $excludeId = null): string {
        $sql    = "SELECT start_time, end_time FROM equipment_reservations
                   WHERE equipment_id = ? AND date = ? AND status = 'active'
                   AND start_time < ? AND end_time > ?";
        $params = [$equipmentId, $date, $endTime, $startTime];
        if ($excludeId !== null) {
            $sql    .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $reservations = $stmt->fetchAll();

        // Build events clipped to the requested window
        $events       = [];
        $initialCount = 0;

        foreach ($reservations as $res) {
            if ($res['start_time'] <= $startTime) {
                $initialCount++;
                if ($res['end_time'] < $endTime) {
                    $events[] = [$res['end_time'], -1];
                }
            } else {
                $events[] = [$res['start_time'], +1];
                if ($res['end_time'] < $endTime) {
                    $events[] = [$res['end_time'], -1];
                }
            }
        }

        // Sort: by time, then end events (-1) before start events (+1)
        usort($events, fn($a, $b) => strcmp($a[0], $b[0]) ?: $a[1] - $b[1]);

        $count               = $initialCount;
        $prevTime            = $startTime;
        $availableSegments   = [];
        $unavailableSegments = [];

        foreach ($events as [$time, $delta]) {
            if ($time > $prevTime) {
                if ($count >= $quantity) {
                    $unavailableSegments[] = [$prevTime, $time];
                } else {
                    $availableSegments[] = [$prevTime, $time];
                }
            }
            $count   += $delta;
            $prevTime = $time;
        }

        if ($prevTime < $endTime) {
            if ($count >= $quantity) {
                $unavailableSegments[] = [$prevTime, $endTime];
            } else {
                $availableSegments[] = [$prevTime, $endTime];
            }
        }

        if (empty($availableSegments)) {
            return 'error:No units available during that time slot.';
        }

        if (count($availableSegments) === 1) {
            [$from, $to] = $availableSegments[0];
            // Occupied at the start, free until the end of the window
            if (!empty($unavailableSegments) && $unavailableSegments[0][0] === $startTime && $to === $endTime) {
                return 'error:All units are occupied until ' . $from . '.';
            }
            return 'error:Units are only available from ' . $from . ' to ' . $to . ' during your requested time.';
        }

        $parts = array_map(fn($s) => $s[0] . '–' . $s[1], $availableSegments);
        return 'error:Units are only available at: ' . implode(', ', $parts) . '.';
    }

    private static function validateSlot(string $date, string $startTime, string $endTime): ?string {
        if ($date < date('Y-m-d')) {
            return 'error:You can only reserve equipment for today or future dates.';
        }
        if ($date > date('Y-m-d', strtotime('+7 days'))) {
            return 'error:You can only reserve equipment up to one week in advance.';
        }
        if ($date === date('Y-m-d') && $startTime < date('H:i')) {
            return 'error:Start time cannot be in the past.';
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
        return null;
    }

    static function create(PDO $db, int $userId, int $equipmentId, string $date, string $startTime, string $endTime): string {
        $err = self::validateSlot($date, $startTime, $endTime);
        if ($err) return $err;

        $stmt = $db->prepare('SELECT quantity, in_use FROM equipment WHERE id = ?');
        $stmt->execute([$equipmentId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'error:Equipment not found.';
        }
        $quantity = (int)$row['quantity'];
        $inUse    = (int)$row['in_use'];

        // Check double-booking by the same user for the same equipment
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM equipment_reservations
             WHERE user_id = ? AND equipment_id = ? AND date = ? AND status = 'active'
             AND start_time < ? AND end_time > ?"
        );
        $stmt->execute([$userId, $equipmentId, $date, $endTime, $startTime]);
        if ((int)$stmt->fetchColumn() > 0) {
            return 'error:You already have a reservation for this equipment at that time.';
        }

        $overlapping = self::countOverlapping($db, $equipmentId, $date, $startTime, $endTime);

        // For slots that include the current moment, walk-in users occupy units too
        $walkins     = ($date === date('Y-m-d') && $startTime <= date('H:i') && $endTime > date('H:i')) ? $inUse : 0;
        $effectiveQty = $quantity - $walkins;

        if ($overlapping >= $effectiveQty) {
            if ($effectiveQty <= 0) {
                return 'error:All units are currently in use. Please try a later time slot.';
            }
            return self::conflictMessage($db, $equipmentId, $date, $startTime, $endTime, $effectiveQty);
        }

        $stmt = $db->prepare(
            'INSERT INTO equipment_reservations (user_id, equipment_id, date, start_time, end_time)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $equipmentId, $date, $startTime, $endTime]);
        return 'success:Reservation confirmed!';
    }

    static function update(PDO $db, int $id, int $userId, string $date, string $startTime, string $endTime): string {
        $stmt = $db->prepare("SELECT * FROM equipment_reservations WHERE id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$id, $userId]);
        $existing = $stmt->fetch();
        if (!$existing) {
            return 'error:Reservation not found.';
        }

        $err = self::validateSlot($date, $startTime, $endTime);
        if ($err) return $err;

        $equipmentId = (int)$existing['equipment_id'];

        $stmt = $db->prepare('SELECT quantity, in_use FROM equipment WHERE id = ?');
        $stmt->execute([$equipmentId]);
        $eqRow    = $stmt->fetch();
        $quantity = (int)$eqRow['quantity'];
        $inUse    = (int)$eqRow['in_use'];

        // Check double-booking by the same user for the same equipment (excluding this reservation)
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM equipment_reservations
             WHERE user_id = ? AND equipment_id = ? AND date = ? AND status = 'active'
             AND start_time < ? AND end_time > ? AND id != ?"
        );
        $stmt->execute([$userId, $equipmentId, $date, $endTime, $startTime, $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return 'error:You already have a reservation for this equipment at that time.';
        }

        $overlapping  = self::countOverlapping($db, $equipmentId, $date, $startTime, $endTime, $id);
        $walkins      = ($date === date('Y-m-d') && $startTime <= date('H:i') && $endTime > date('H:i')) ? $inUse : 0;
        $effectiveQty = $quantity - $walkins;

        if ($overlapping >= $effectiveQty) {
            if ($effectiveQty <= 0) {
                return 'error:All units are currently in use. Please try a later time slot.';
            }
            return self::conflictMessage($db, $equipmentId, $date, $startTime, $endTime, $effectiveQty, $id);
        }

        $stmt = $db->prepare(
            'UPDATE equipment_reservations SET date = ?, start_time = ?, end_time = ? WHERE id = ?'
        );
        $stmt->execute([$date, $startTime, $endTime, $id]);
        return 'success:Reservation updated.';
    }

    static function stop(PDO $db, int $id, int $userId): string {
        $stmt = $db->prepare("SELECT * FROM equipment_reservations WHERE id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'error:Reservation not found.';
        }
        $now = date('H:i');
        if ($row['date'] !== date('Y-m-d') || $row['start_time'] > $now || $row['end_time'] <= $now) {
            return 'error:This reservation is not currently active.';
        }
        $stmt = $db->prepare('UPDATE equipment_reservations SET end_time = ? WHERE id = ?');
        $stmt->execute([$now, $id]);
        return 'success:Reservation ended.';
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
