<?php
declare(strict_types=1);

class Equipment {
    public int $id;
    public string $name;
    public string $type;
    public int $quantity;
    public bool $available;

    public function __construct(int $id, string $name, string $type, int $quantity, bool $available) {
        $this->id        = $id;
        $this->name      = $name;
        $this->type      = $type;
        $this->quantity  = $quantity;
        $this->available = $available;
    }

    private static function fromRow(array $row): Equipment {
        return new Equipment(
            (int)$row['id'], $row['name'], $row['type'],
            (int)$row['quantity'], (bool)$row['available']
        );
    }

    static function getAll(PDO $db): array {
        return array_map(
            [self::class, 'fromRow'],
            $db->query('SELECT * FROM equipment ORDER BY type, name')->fetchAll()
        );
    }

    static function getById(PDO $db, int $id): ?Equipment {
        $stmt = $db->prepare('SELECT * FROM equipment WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    static function create(PDO $db, string $name, string $type, int $quantity, bool $available): void {
        $stmt = $db->prepare('INSERT INTO equipment (name, type, quantity, available) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $type, $quantity, (int)$available]);
    }

    function save(PDO $db): void {
        $stmt = $db->prepare('UPDATE equipment SET name = ?, type = ?, quantity = ?, available = ? WHERE id = ?');
        $stmt->execute([$this->name, $this->type, $this->quantity, (int)$this->available, $this->id]);
    }

    static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM equipment WHERE id = ?');
        $stmt->execute([$id]);
    }

    static function getTypes(PDO $db): array {
        return $db->query('SELECT DISTINCT type FROM equipment ORDER BY type')->fetchAll(PDO::FETCH_COLUMN);
    }
}
