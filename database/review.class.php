<?php
declare(strict_types=1);

class Review {
    public int $id;
    public int $class_id;
    public int $user_id;
    public string $author_name;
    public int $rating;
    public string $comment;
    public string $created_at;
    public string $class_name;
    public string $class_day;
    public string $class_time;

    public function __construct(
        int $id, int $class_id, int $user_id,
        int $rating, string $comment, string $created_at,
        string $author_name = '', string $class_name = '', string $class_day = '', string $class_time = ''
    ) {
        $this->id          = $id;
        $this->class_id    = $class_id;
        $this->user_id     = $user_id;
        $this->author_name = $author_name;
        $this->rating      = $rating;
        $this->comment     = $comment;
        $this->created_at  = $created_at;
        $this->class_name  = $class_name;
        $this->class_day   = $class_day;
        $this->class_time  = $class_time;
    }

    private static function fromRow(array $r): Review {
        return new Review(
            (int)$r['id'],
            (int)$r['class_id'],
            (int)$r['user_id'],
            (int)$r['rating'],
            $r['comment'],
            $r['created_at'] ?? '',
            $r['user_name']  ?? '',
            $r['class_name'] ?? '',
            $r['class_day']  ?? '',
            $r['class_time'] ?? ''
        );
    }

    static function getByClass(PDO $db, int $class_id): array {
        $stmt = $db->prepare(
            'SELECT r.id, r.class_id, r.user_id, r.rating, r.comment, r.created_at,
                    u.name AS user_name,
                    c.name AS class_name, c.day_of_week AS class_day, c.time AS class_time
             FROM reviews r
             JOIN users   u ON u.id = r.user_id
             JOIN classes c ON c.id = r.class_id
             WHERE r.class_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$class_id]);
        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    static function hasReviewed(PDO $db, int $user_id, int $class_id): bool {
        $stmt = $db->prepare('SELECT 1 FROM reviews WHERE user_id = ? AND class_id = ?');
        $stmt->execute([$user_id, $class_id]);
        return (bool)$stmt->fetchColumn();
    }

    static function create(PDO $db, int $class_id, int $user_id, int $rating, string $comment): string {
        if ($rating < 1 || $rating > 5) return 'error:Rating must be between 1 and 5.';
        if (trim($comment) === '')       return 'error:Please write a comment.';
        if (self::hasReviewed($db, $user_id, $class_id)) return 'error:You have already reviewed this class.';

        $stmt = $db->prepare(
            'INSERT INTO reviews (class_id, user_id, rating, comment)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$class_id, $user_id, $rating, $comment]);
        return 'success:Your review has been submitted. Thank you!';
    }
}