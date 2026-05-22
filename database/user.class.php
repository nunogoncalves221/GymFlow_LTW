<?php
declare(strict_types=1);

class User {
    public int $id;
    public string $name;
    public string $username;
    public string $type;
    public ?string $profile_picture;

    public function __construct(int $id, string $name, string $username, string $type, ?string $profile_picture = null) {
        $this->id              = $id;
        $this->name            = $name;
        $this->username        = $username;
        $this->type            = $type;
        $this->profile_picture = $profile_picture;
    }

    static function getUserWithPassword(PDO $db, string $username, string $password): ?User {
        $stmt = $db->prepare('SELECT id, name, username, type, profile_picture FROM users WHERE username = ? AND password = ?');
        $stmt->execute([$username, sha1($password)]);
        $row = $stmt->fetch();
        if ($row) return new User($row['id'], $row['name'], $row['username'], $row['type'], $row['profile_picture']);
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

    function saveProfilePicture(PDO $db, ?string $path): void {
        $stmt = $db->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
        $stmt->execute([$path, $this->id]);
        $this->profile_picture = $path;
    }

    function getAvatarPath(): string {
        if ($this->profile_picture !== null && $this->profile_picture !== '') {
            return $this->profile_picture;
        }
        return '/docs/profile_pictures/default.jpg';
    }

    static function getUser(PDO $db, int $id): ?User {
        $stmt = $db->prepare('SELECT id, name, username, type, profile_picture FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new User($row['id'], $row['name'], $row['username'], $row['type'], $row['profile_picture']) : null;
    }

    static function getAll(PDO $db): array {
        $rows = $db->query('SELECT id, name, username, type, profile_picture FROM users ORDER BY type, name')->fetchAll();
        return array_map(fn($r) => new User($r['id'], $r['name'], $r['username'], $r['type'], $r['profile_picture']), $rows);
    }

    static function getByType(PDO $db, string $type): array {
        $stmt = $db->prepare('SELECT id, name, username, type, profile_picture FROM users WHERE type = ? ORDER BY name');
        $stmt->execute([$type]);
        return array_map(fn($r) => new User($r['id'], $r['name'], $r['username'], $r['type'], $r['profile_picture']), $stmt->fetchAll());
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
