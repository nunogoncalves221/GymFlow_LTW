<?php
declare(strict_types=1);

class NutritionMeal {
    public int $id;
    public int $nutrition_plan_id;
    public string $name;
    public string $time_of_day;
    public int $calories;
    public string $notes;

    public function __construct(
        int $id, int $nutrition_plan_id, string $name,
        string $time_of_day, int $calories, string $notes
    ) {
        $this->id                = $id;
        $this->nutrition_plan_id = $nutrition_plan_id;
        $this->name              = $name;
        $this->time_of_day       = $time_of_day;
        $this->calories          = $calories;
        $this->notes             = $notes;
    }

    private static function fromRow(array $row): NutritionMeal {
        return new NutritionMeal(
            (int)$row['id'], (int)$row['nutrition_plan_id'],
            $row['name'], $row['time_of_day'],
            (int)$row['calories'], $row['notes']
        );
    }

    static function getByPlan(PDO $db, int $plan_id): array {
        $stmt = $db->prepare('SELECT * FROM nutrition_meals WHERE nutrition_plan_id = ? ORDER BY id');
        $stmt->execute([$plan_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }
}

class NutritionPlan {
    public int $id;
    public int $trainer_id;
    public int $member_id;
    public string $title;
    public string $description;
    public ?int $calories;
    public string $created_at;
    public ?string $member_name;

    public function __construct(
        int $id, int $trainer_id, int $member_id,
        string $title, string $description, ?int $calories,
        string $created_at, ?string $member_name = null
    ) {
        $this->id          = $id;
        $this->trainer_id  = $trainer_id;
        $this->member_id   = $member_id;
        $this->title       = $title;
        $this->description = $description;
        $this->calories    = $calories;
        $this->created_at  = $created_at;
        $this->member_name = $member_name;
    }

    private static function fromRow(array $row): NutritionPlan {
        return new NutritionPlan(
            (int)$row['id'], (int)$row['trainer_id'], (int)$row['member_id'],
            $row['title'], $row['description'],
            isset($row['calories']) ? (int)$row['calories'] : null,
            $row['created_at'],
            $row['member_name'] ?? null
        );
    }

    static function getById(PDO $db, int $id): ?NutritionPlan {
        $stmt = $db->prepare(
            'SELECT np.*, u.name AS member_name
             FROM nutrition_plans np
             JOIN users u ON u.id = np.member_id
             WHERE np.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    static function getByTrainer(PDO $db, int $trainer_id): array {
        $stmt = $db->prepare(
            'SELECT np.*, u.name AS member_name
             FROM nutrition_plans np
             JOIN users u ON u.id = np.member_id
             WHERE np.trainer_id = ?
             ORDER BY np.created_at DESC'
        );
        $stmt->execute([$trainer_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    static function getByMember(PDO $db, int $member_id): array {
        $stmt = $db->prepare(
            'SELECT np.*, t.name AS member_name
             FROM nutrition_plans np
             JOIN trainers t ON t.id = np.trainer_id
             WHERE np.member_id = ?
             ORDER BY np.created_at DESC'
        );
        $stmt->execute([$member_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    static function create(
        PDO $db, int $trainer_id, int $member_id,
        string $title, string $description, ?int $calories,
        array $meals
    ): int {
        $stmt = $db->prepare(
            'INSERT INTO nutrition_plans (trainer_id, member_id, title, description, calories)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$trainer_id, $member_id, $title, $description, $calories]);
        $plan_id = (int)$db->lastInsertId();

        foreach ($meals as $meal) {
            $stmt2 = $db->prepare(
                'INSERT INTO nutrition_meals (nutrition_plan_id, name, time_of_day, calories, notes)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt2->execute([
                $plan_id,
                $meal['name'],
                $meal['time_of_day'],
                (int)($meal['calories'] ?? 0),
                $meal['notes'] ?? ''
            ]);
        }
        return $plan_id;
    }

    static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM nutrition_plans WHERE id = ?');
        $stmt->execute([$id]);
    }

    function getMeals(PDO $db): array {
        return NutritionMeal::getByPlan($db, $this->id);
    }
}
