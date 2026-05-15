<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/database.db');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

function h(mixed $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function initials(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    return strtoupper(substr(reset($parts) ?: '', 0, 1) . substr(end($parts) ?: '', 0, 1));
}
