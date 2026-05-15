<?php
declare(strict_types=1);

class Trainer {
    public int $id;
    public string $name;
    public string $bio;
    public string $specializations;
    public string $certifications;
    public ?int $user_id;

    public function __construct(
        int $id, string $name, string $bio,
        string $specializations, string $certifications, ?int $user_id
    ) {
        $this->id              = $id;
        $this->name            = $name;
        $this->bio             = $bio;
        $this->specializations = $specializations;
        $this->certifications  = $certifications;
        $this->user_id         = $user_id;
    }

    private static function fromRow(array $row): Trainer {
        return new Trainer(
            $row['id'], $row['name'], $row['bio'],
            $row['specializations'], $row['certifications'],
            isset($row['user_id']) ? (int)$row['user_id'] : null
        );
    }

    static function getTrainer(PDO $db, int $id): ?Trainer {
        $stmt = $db->prepare('SELECT * FROM trainers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    static function getTrainerByUserId(PDO $db, int $user_id): ?Trainer {
        $stmt = $db->prepare('SELECT * FROM trainers WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    static function getTrainers(PDO $db): array {
        return array_map(
            [self::class, 'fromRow'],
            $db->query('SELECT * FROM trainers ORDER BY name')->fetchAll()
        );
    }
}
