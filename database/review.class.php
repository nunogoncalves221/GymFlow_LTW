<?php
declare(strict_types=1);

class Review {
    public int $id;
    public int $class_id;
    public string $author_name;
    public int $rating;
    public string $comment;

    public function __construct(
        int $id, int $class_id, string $author_name, int $rating, string $comment
    ) {
        $this->id          = $id;
        $this->class_id    = $class_id;
        $this->author_name = $author_name;
        $this->rating      = $rating;
        $this->comment     = $comment;
    }

    static function getByClass(PDO $db, int $class_id): array {
        $stmt = $db->prepare('SELECT * FROM reviews WHERE class_id = ?');
        $stmt->execute([$class_id]);
        return array_map(
            fn($r) => new Review($r['id'], $r['class_id'], $r['author_name'], $r['rating'], $r['comment']),
            $stmt->fetchAll()
        );
    }
}
