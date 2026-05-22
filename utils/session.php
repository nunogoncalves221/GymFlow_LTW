<?php
declare(strict_types=1);

class Session {
    private array $messages;

    public function __construct() {
        session_start();
        $this->messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['id']);
    }

    public function logout(): void {
        session_destroy();
    }

    public function getId(): ?int {
        return $_SESSION['id'] ?? null;
    }

    public function getName(): ?string {
        return $_SESSION['name'] ?? null;
    }

    public function getType(): ?string {
        return $_SESSION['type'] ?? null;
    }

    public function setId(int $id): void {
        $_SESSION['id'] = $id;
    }

    public function setName(string $name): void {
        $_SESSION['name'] = $name;
    }

    public function setType(string $type): void {
        $_SESSION['type'] = $type;
    }

    public function addMessage(string $type, string $text): void {
        $_SESSION['messages'][] = ['type' => $type, 'text' => $text];
    }

    public function getMessages(): array {
        return $this->messages;
    }
}
