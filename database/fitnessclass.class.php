<?php
declare(strict_types=1);

class FitnessClass {
    public int $id;
    public string $name;
    public string $type;
    public string $description;
    public int $duration_min;
    public int $capacity;
    public ?int $trainer_id;
    public string $trainer_name;
    public ?string $image;
    public string $day_of_week;
    public string $time;

    public function __construct(
        int $id, string $name, string $type, string $description,
        int $duration_min, int $capacity, ?int $trainer_id, string $trainer_name,
        ?string $image, string $day_of_week, string $time
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->type         = $type;
        $this->description  = $description;
        $this->duration_min = $duration_min;
        $this->capacity     = $capacity;
        $this->trainer_id   = $trainer_id;
        $this->trainer_name = $trainer_name;
        $this->image        = $image;
        $this->day_of_week  = $day_of_week;
        $this->time         = $time;
    }

    private static function fromRow(array $row): FitnessClass {
        return new FitnessClass(
            $row['id'], $row['name'], $row['type'], $row['description'],
            (int)$row['duration_min'], (int)$row['capacity'],
            isset($row['trainer_id']) ? (int)$row['trainer_id'] : null,
            $row['trainer_name'] ?? '',
            $row['image'] ?? null,
            $row['day_of_week'], $row['time']
        );
    }

    private static function dayOrder(): string {
        return 'CASE day_of_week
            WHEN "Monday" THEN 1 WHEN "Tuesday" THEN 2 WHEN "Wednesday" THEN 3
            WHEN "Thursday" THEN 4 WHEN "Friday" THEN 5
            WHEN "Saturday" THEN 6 WHEN "Sunday" THEN 7 END';
    }

    static function getClasses(PDO $db, string $type = '', string $day = '', string $trainer = '', string $time = ''): array {
        $where  = ['1=1'];
        $params = [];
        if ($type)    { $where[] = 'c.type = ?';           $params[] = $type; }
        if ($day)     { $where[] = 'c.day_of_week = ?';    $params[] = $day; }
        if ($trainer) { $where[] = 'c.trainer_id = ?';     $params[] = (int)$trainer; }
        if ($time)    { $where[] = 'c.time >= ? AND c.time < ?';
                        [$from, $to] = self::timeSlotRange($time);
                        $params[] = $from; $params[] = $to; }

        $stmt = $db->prepare(
            'SELECT c.*, t.name AS trainer_name
             FROM classes c
             LEFT JOIN trainers t ON t.id = c.trainer_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY ' . self::dayOrder() . ', c.time'
        );
        $stmt->execute($params);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    private static function timeSlotRange(string $slot): array {
        return match($slot) {
            'morning'   => ['06:00', '12:00'],
            'afternoon' => ['12:00', '17:00'],
            'evening'   => ['17:00', '24:00'],
            default     => ['00:00', '24:00'],
        };
    }

    static function getTrainersForFilter(PDO $db): array {
        return $db->query('SELECT id, name FROM trainers ORDER BY name')->fetchAll();
    }

    static function getClass(PDO $db, int $id): ?FitnessClass {
        $stmt = $db->prepare(
            'SELECT c.*, t.name AS trainer_name
             FROM classes c
             LEFT JOIN trainers t ON t.id = c.trainer_id
             WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    static function getTypes(PDO $db): array {
        return $db->query('SELECT DISTINCT type FROM classes ORDER BY type')->fetchAll(PDO::FETCH_COLUMN);
    }

    static function create(PDO $db, string $name, string $type, string $description, int $duration_min, int $capacity, ?int $trainer_id, ?string $image, string $day_of_week, string $time): int {
        $stmt = $db->prepare('INSERT INTO classes (name, type, description, duration_min, capacity, trainer_id, image, day_of_week, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $type, $description, $duration_min, $capacity, $trainer_id, $image, $day_of_week, $time]);
        return (int)$db->lastInsertId();
    }

    function save(PDO $db): void {
        $stmt = $db->prepare('UPDATE classes SET name = ?, type = ?, description = ?, duration_min = ?, capacity = ?, trainer_id = ?, image = ?, day_of_week = ?, time = ? WHERE id = ?');
        $stmt->execute([$this->name, $this->type, $this->description, $this->duration_min, $this->capacity, $this->trainer_id, $this->image, $this->day_of_week, $this->time, $this->id]);
    }

    static function delete(PDO $db, int $id): void {
        $db->prepare('DELETE FROM reviews WHERE class_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM classes WHERE id = ?')->execute([$id]);
    }

    static function getByTrainer(PDO $db, int $trainer_id): array {
        $stmt = $db->prepare(
            'SELECT c.*, t.name AS trainer_name
             FROM classes c
             LEFT JOIN trainers t ON t.id = c.trainer_id
             WHERE c.trainer_id = ?
             ORDER BY ' . self::dayOrder() . ', c.time'
        );
        $stmt->execute([$trainer_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    // --- Enrollment methods ---

    static function getEnrolledCount(PDO $db, int $class_id): int {
        $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE class_id = ?');
        $stmt->execute([$class_id]);
        return (int)$stmt->fetchColumn();
    }

    static function isEnrolled(PDO $db, int $user_id, int $class_id): bool {
        $stmt = $db->prepare('SELECT 1 FROM enrollments WHERE user_id = ? AND class_id = ?');
        $stmt->execute([$user_id, $class_id]);
        return (bool)$stmt->fetchColumn();
    }

    static function enroll(PDO $db, int $user_id, int $class_id): string {
        $class = self::getClass($db, $class_id);
        if (!$class) return 'error:Class not found.';

        $enrolled = self::getEnrolledCount($db, $class_id);
        if ($enrolled >= $class->capacity) return 'error:This class is full.';

        if (self::isEnrolled($db, $user_id, $class_id)) return 'error:You are already enrolled in this class.';

        try {
            $db->prepare('INSERT INTO enrollments (user_id, class_id) VALUES (?, ?)')->execute([$user_id, $class_id]);
            return 'success:You have been enrolled in ' . $class->name . '!';
        } catch (PDOException) {
            return 'error:Could not complete enrollment. Please try again.';
        }
    }

    static function cancelEnrollment(PDO $db, int $user_id, int $class_id): string {
        $class = self::getClass($db, $class_id);
        if (!$class) return 'error:Class not found.';

        if (!self::isEnrolled($db, $user_id, $class_id)) return 'error:You are not enrolled in this class.';

        $db->prepare('DELETE FROM enrollments WHERE user_id = ? AND class_id = ?')->execute([$user_id, $class_id]);
        return 'success:Your enrollment in ' . $class->name . ' has been cancelled.';
    }

    static function getEnrollmentsForUser(PDO $db, int $user_id): array {
        $stmt = $db->prepare(
            'SELECT c.*, t.name AS trainer_name, e.created_at AS enrolled_at
            FROM enrollments e
            JOIN classes c ON c.id = e.class_id
            LEFT JOIN trainers t ON t.id = c.trainer_id
            WHERE e.user_id = ?
            ORDER BY ' . self::dayOrder() . ', c.time'
        );
        $stmt->execute([$user_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }
}