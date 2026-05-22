<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'member') {
    $session->addMessage('error', 'You must be logged in as a member to submit a review.');
    header('Location: /pages/login.php');
    exit;
}

require_once('../database/connection.db.php');
require_once('../database/fitnessclass.class.php');
require_once('../database/review.class.php');

$db       = getDatabaseConnection();
$class_id = (int)($_POST['class_id'] ?? 0);
$rating   = (int)($_POST['rating']   ?? 0);
$comment  = trim($_POST['comment']   ?? '');

if (!$class_id) {
    $session->addMessage('error', 'Invalid class.');
    header('Location: /pages/index.php');
    exit;
}

// Only enrolled members may review
if (!FitnessClass::isEnrolled($db, $session->getId(), $class_id)) {
    $session->addMessage('error', 'You must be enrolled in a class to review it.');
    header('Location: /pages/class.php?id=' . $class_id);
    exit;
}

$result = Review::create($db, $class_id, $session->getId(), $session->getName(), $rating, $comment);
[$type, $text] = explode(':', $result, 2);
$session->addMessage($type, $text);

header('Location: /pages/class.php?id=' . $class_id);
exit;