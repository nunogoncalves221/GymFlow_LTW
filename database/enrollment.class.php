<?php
declare(strict_types=1);

class Enrollment {
    static function countEnrolled(PDO $db, int $class_id): int {
        $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE class_id = ?');
        $stmt->execute([$class_id]);
        return (int)$stmt->fetchColumn();
    }

    static function isEnrolled(PDO $db, int $class_id, int $user_id): bool {
        $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE class_id = ? AND user_id = ?');
        $stmt->execute([$class_id, $user_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    static function enroll(PDO $db, int $class_id, int $user_id): void {
        $stmt = $db->prepare('INSERT INTO enrollments (class_id, user_id) VALUES (?, ?)');
        $stmt->execute([$class_id, $user_id]);
    }

    static function cancel(PDO $db, int $class_id, int $user_id): void {
        $stmt = $db->prepare('DELETE FROM enrollments WHERE class_id = ? AND user_id = ?');
        $stmt->execute([$class_id, $user_id]);
    }

    static function getEnrolledMembers(PDO $db, int $class_id): array {
        $stmt = $db->prepare(
            'SELECT u.id, u.name, u.username
             FROM enrollments e
             JOIN users u ON u.id = e.user_id
             WHERE e.class_id = ?
             ORDER BY u.name'
        );
        $stmt->execute([$class_id]);
        return $stmt->fetchAll();
    }
}
