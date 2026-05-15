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
}
