<?php
declare(strict_types=1);

class Dispute {
    public int $id;
    public int $user_id;
    public string $author_name;
    public string $category;
    public string $description;
    public string $status;
    public ?string $admin_response;
    public string $created_at;
    public ?string $responded_at;

    public function __construct(
        int $id, int $user_id, string $author_name,
        string $category, string $description,
        string $status, ?string $admin_response,
        string $created_at, ?string $responded_at = null
    ) {
        $this->id             = $id;
        $this->user_id        = $user_id;
        $this->author_name    = $author_name;
        $this->category       = $category;
        $this->description    = $description;
        $this->status         = $status;
        $this->admin_response = $admin_response;
        $this->created_at     = $created_at;
        $this->responded_at   = $responded_at;
    }

    private static function fromRow(array $r): Dispute {
        return new Dispute(
            (int)$r['id'],
            (int)$r['user_id'],
            $r['author_name'] ?? '',
            $r['category'],
            $r['description'],
            $r['status'],
            $r['admin_response'] ?? null,
            $r['created_at'],
            $r['responded_at'] ?? null
        );
    }

    static function getAll(PDO $db): array {
        $stmt = $db->query(
            'SELECT d.*, u.name AS author_name
             FROM disputes d
             JOIN users u ON u.id = d.user_id
             ORDER BY d.created_at DESC'
        );
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    static function getByUser(PDO $db, int $user_id): array {
        $stmt = $db->prepare(
            'SELECT d.*, u.name AS author_name
             FROM disputes d
             JOIN users u ON u.id = d.user_id
             WHERE d.user_id = ?
             ORDER BY d.created_at DESC'
        );
        $stmt->execute([$user_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    static function getById(PDO $db, int $id): ?Dispute {
        $stmt = $db->prepare(
            'SELECT d.*, u.name AS author_name
             FROM disputes d
             JOIN users u ON u.id = d.user_id
             WHERE d.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    static function create(PDO $db, int $user_id, string $category, string $description): string {
        $category    = trim($category);
        $description = trim($description);
        if ($category    === '') return 'error:Please select a category.';
        if ($description === '') return 'error:Please describe the issue.';
        if (strlen($description) < 10) return 'error:Description is too short.';

        $stmt = $db->prepare(
            'INSERT INTO disputes (user_id, category, description) VALUES (?, ?, ?)'
        );
        $stmt->execute([$user_id, $category, $description]);
        return 'success:Your report has been submitted. An admin will review it soon.';
    }

    static function respond(PDO $db, int $id, string $response, string $status): string {
        $response = trim($response);
        $allowed  = ['open', 'in_review', 'resolved'];
        if (!in_array($status, $allowed, true)) return 'error:Invalid status.';
        if ($response === '')                    return 'error:Please write a response.';

        $stmt = $db->prepare(
            'UPDATE disputes
             SET admin_response = ?, status = ?, responded_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $stmt->execute([$response, $status, $id]);
        return 'success:Response saved.';
    }

    static function updateStatus(PDO $db, int $id, string $status): string {
        $allowed = ['open', 'in_review', 'resolved'];
        if (!in_array($status, $allowed, true)) return 'error:Invalid status.';

        $stmt = $db->prepare('UPDATE disputes SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        return 'success:Status updated.';
    }

    static function countByStatus(PDO $db, string $status): int {
        $stmt = $db->prepare('SELECT COUNT(*) FROM disputes WHERE status = ?');
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }
} 