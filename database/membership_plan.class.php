<?php
declare(strict_types=1);

class MembershipPlan {
    public int    $id;
    public string $name;
    public float  $price;
    public string $description;
    public ?int   $max_enrollments_per_week;
    public int    $access_level;
    public bool   $is_active;
    public array  $features = [];

    public function __construct(
        int $id, string $name, float $price, string $description,
        ?int $max_enrollments_per_week, int $access_level, bool $is_active
    ) {
        $this->id                       = $id;
        $this->name                     = $name;
        $this->price                    = $price;
        $this->description              = $description;
        $this->max_enrollments_per_week = $max_enrollments_per_week;
        $this->access_level             = $access_level;
        $this->is_active                = $is_active;
    }

    private static function fromRow(array $row): self {
        return new self(
            (int)$row['id'],
            $row['name'],
            (float)$row['price'],
            $row['description'],
            isset($row['max_enrollments_per_week']) ? (int)$row['max_enrollments_per_week'] : null,
            (int)$row['access_level'],
            (bool)$row['is_active']
        );
    }

    static function getAll(PDO $db, bool $activeOnly = false): array {
        $sql  = 'SELECT * FROM membership_plans' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY access_level';
        $rows = $db->query($sql)->fetchAll();
        $plans = array_map(fn($r) => self::fromRow($r), $rows);
        foreach ($plans as $plan) {
            $plan->features = self::getFeatures($db, $plan->id);
        }
        return $plans;
    }

    static function getById(PDO $db, int $id): ?self {
        $stmt = $db->prepare('SELECT * FROM membership_plans WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $plan = self::fromRow($row);
        $plan->features = self::getFeatures($db, $plan->id);
        return $plan;
    }

    static function getFeatures(PDO $db, int $plan_id): array {
        $stmt = $db->prepare('SELECT feature FROM membership_plan_features WHERE plan_id = ? ORDER BY id');
        $stmt->execute([$plan_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    static function create(PDO $db, string $name, float $price, string $description, ?int $max_per_week, int $access_level, array $features): int {
        $stmt = $db->prepare(
            'INSERT INTO membership_plans (name, price, description, max_enrollments_per_week, access_level) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $price, $description, $max_per_week, $access_level]);
        $id = (int)$db->lastInsertId();
        self::saveFeatures($db, $id, $features);
        return $id;
    }

    function save(PDO $db, array $features): void {
        $stmt = $db->prepare(
            'UPDATE membership_plans SET name=?, price=?, description=?, max_enrollments_per_week=?, access_level=?, is_active=? WHERE id=?'
        );
        $stmt->execute([
            $this->name, $this->price, $this->description,
            $this->max_enrollments_per_week, $this->access_level,
            $this->is_active ? 1 : 0, $this->id
        ]);
        self::saveFeatures($db, $this->id, $features);
    }

    private static function saveFeatures(PDO $db, int $plan_id, array $features): void {
        $db->prepare('DELETE FROM membership_plan_features WHERE plan_id = ?')->execute([$plan_id]);
        $stmt = $db->prepare('INSERT INTO membership_plan_features (plan_id, feature) VALUES (?, ?)');
        foreach (array_filter(array_map('trim', $features)) as $f) {
            $stmt->execute([$plan_id, $f]);
        }
    }

    static function getEnrollmentsThisWeek(PDO $db, int $user_id): int {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM enrollments
             WHERE user_id = ?
               AND created_at >= date('now', 'weekday 1', '-7 days')"
        );
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    }

    static function canEnroll(PDO $db, int $user_id, ?int $plan_id): array {
        if ($plan_id === null) {
            return [false, 'You need an active membership plan to enroll in classes.'];
        }
        $plan = self::getById($db, $plan_id);
        if (!$plan || !$plan->is_active) {
            return [false, 'Your membership plan is not active.'];
        }
        if ($plan->max_enrollments_per_week === null) {
            return [true, ''];
        }
        $used = self::getEnrollmentsThisWeek($db, $user_id);
        if ($used >= $plan->max_enrollments_per_week) {
            return [false, "You have reached your weekly limit of {$plan->max_enrollments_per_week} class(es) on the {$plan->name} plan. Upgrade to enroll in more."];
        }
        return [true, ''];
    }

    static function createRequest(PDO $db, int $user_id, int $plan_id): bool {
        $db->prepare("DELETE FROM membership_requests WHERE user_id = ? AND status = 'pending'")->execute([$user_id]);
        $stmt = $db->prepare('INSERT INTO membership_requests (user_id, plan_id) VALUES (?, ?)');
        return $stmt->execute([$user_id, $plan_id]);
    }

    static function getPendingRequestForUser(PDO $db, int $user_id): ?array {
        $stmt = $db->prepare(
            'SELECT mr.*, mp.name as plan_name FROM membership_requests mr
             JOIN membership_plans mp ON mp.id = mr.plan_id
             WHERE mr.user_id = ? AND mr.status = "pending"
             ORDER BY mr.created_at DESC LIMIT 1'
        );
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    static function getPendingRequests(PDO $db): array {
        return $db->query(
            'SELECT mr.*, u.name as user_name, u.username, mp.name as plan_name
             FROM membership_requests mr
             JOIN users u ON u.id = mr.user_id
             JOIN membership_plans mp ON mp.id = mr.plan_id
             WHERE mr.status = "pending"
             ORDER BY mr.created_at ASC'
        )->fetchAll();
    }

    static function approveRequest(PDO $db, int $request_id): bool {
        $stmt = $db->prepare('SELECT * FROM membership_requests WHERE id = ?');
        $stmt->execute([$request_id]);
        $req = $stmt->fetch();
        if (!$req) return false;
        $db->prepare('UPDATE users SET plan_id = ? WHERE id = ?')->execute([$req['plan_id'], $req['user_id']]);
        $db->prepare("UPDATE membership_requests SET status = 'approved' WHERE id = ?")->execute([$request_id]);
        return true;
    }

    static function rejectRequest(PDO $db, int $request_id): bool {
        $stmt = $db->prepare("UPDATE membership_requests SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$request_id]);
    }

    static function setUserPlan(PDO $db, int $user_id, ?int $plan_id): void {
        $db->prepare('UPDATE users SET plan_id = ? WHERE id = ?')->execute([$plan_id, $user_id]);
        $db->prepare("UPDATE membership_requests SET status = 'approved' WHERE user_id = ? AND status = 'pending'")->execute([$user_id]);
    }
}