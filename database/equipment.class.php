<?php
declare(strict_types=1);

class Equipment {
    public int $id;
    public string $name;
    public string $type;
    public int $quantity;
    public int $in_use;

    public function __construct(int $id, string $name, string $type, int $quantity, int $in_use) {
        $this->id       = $id;
        $this->name     = $name;
        $this->type     = $type;
        $this->quantity = $quantity;
        $this->in_use   = $in_use;
    }

    public function isAvailable(): bool {
        return $this->in_use < $this->quantity;
    }

    private static function fromRow(array $row): Equipment {
        return new Equipment(
            (int)$row['id'], $row['name'], $row['type'],
            (int)$row['quantity'], (int)$row['in_use']
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

    static function create(PDO $db, string $name, string $type, int $quantity, int $in_use): void {
        $stmt = $db->prepare('INSERT INTO equipment (name, type, quantity, in_use) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $type, $quantity, $in_use]);
    }

    function save(PDO $db): void {
        $stmt = $db->prepare('UPDATE equipment SET name = ?, type = ?, quantity = ?, in_use = ? WHERE id = ?');
        $stmt->execute([$this->name, $this->type, $this->quantity, $this->in_use, $this->id]);
    }

    static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM equipment WHERE id = ?');
        $stmt->execute([$id]);
    }

    static function getTypes(PDO $db): array {
        return $db->query('SELECT DISTINCT type FROM equipment ORDER BY type')->fetchAll(PDO::FETCH_COLUMN);
    }
}