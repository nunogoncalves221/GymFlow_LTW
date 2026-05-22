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
}