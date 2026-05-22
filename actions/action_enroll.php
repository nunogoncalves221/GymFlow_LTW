<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'You must be logged in to enroll in a class.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());

if ($user->type !== 'member') {
    $session->addMessage('error', 'Only members can enroll in classes.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$class_id = (int)($_POST['class_id'] ?? 0);

$result = FitnessClass::enroll($db, $session->getId(), $class_id);
[$type, $text] = explode(':', $result, 2);

$session->addMessage($type, $text);
header('Location: /pages/class.php?id=' . $class_id);
