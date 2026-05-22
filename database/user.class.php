<?php
declare(strict_types=1);

class User {
    public int $id;
    public string $name;
    public string $username;
    public string $type;

    public function __construct(int $id, string $name, string $username, string $type) {
        $this->id       = $id;
        $this->name     = $name;
        $this->username = $username;
        $this->type     = $type;
    }

    static function getUserWithPassword(PDO $db, string $username, string $password): ?User {
        $stmt = $db->prepare('SELECT id, name, username, type FROM users WHERE username = ? AND password = ?');
        $stmt->execute([$username, sha1($password)]);
        $row = $stmt->fetch();
        if ($row) return new User($row['id'], $row['name'], $row['username'], $row['type']);
        return null;
    }

    static function register(PDO $db, string $name, string $username, string $password): bool {
        try {
            $stmt = $db->prepare('INSERT INTO users (name, username, password, type) VALUES (?, ?, ?, \'member\')');
            $stmt->execute([$name, $username, sha1($password)]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    function save(PDO $db): void {
        $stmt = $db->prepare('UPDATE users SET name = ?, username = ? WHERE id = ?');
        $stmt->execute([$this->name, $this->username, $this->id]);
    }

    static function getUser(PDO $db, int $id): ?User {
        $stmt = $db->prepare('SELECT id, name, username, type FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new User($row['id'], $row['name'], $row['username'], $row['type']) : null;
    }

    static function getAll(PDO $db): array {
        $rows = $db->query('SELECT id, name, username, type FROM users ORDER BY type, name')->fetchAll();
        return array_map(fn($r) => new User($r['id'], $r['name'], $r['username'], $r['type']), $rows);
    }

    static function getByType(PDO $db, string $type): array {
        $stmt = $db->prepare('SELECT id, name, username, type FROM users WHERE type = ? ORDER BY name');
        $stmt->execute([$type]);
        return array_map(fn($r) => new User($r['id'], $r['name'], $r['username'], $r['type']), $stmt->fetchAll());
    }

    function setType(PDO $db, string $type): void {
        $stmt = $db->prepare('UPDATE users SET type = ? WHERE id = ?');
        $stmt->execute([$type, $this->id]);
        $this->type = $type;
    }

    static function createWithType(PDO $db, string $name, string $username, string $password, string $type): bool {
        try {
            $stmt = $db->prepare('INSERT INTO users (name, username, password, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $username, sha1($password), $type]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    static function usernameExists(PDO $db, string $username, int $excludeId = 0): bool {
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $excludeId]);
        return (bool)$stmt->fetchColumn();
    }
}
