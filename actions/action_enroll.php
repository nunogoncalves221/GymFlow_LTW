<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }
if ($session->getType() !== 'member') { header('Location: /pages/index.php'); exit; }

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');

$db       = getDatabaseConnection();
$class_id = (int)($_POST['class_id'] ?? 0);
$action   = $_POST['action'] ?? '';

$class = FitnessClass::getClass($db, $class_id);
if (!$class) { header('Location: /pages/index.php'); exit; }

if ($action === 'enroll') {
    $enrolled = Enrollment::countEnrolled($db, $class_id);
    if ($enrolled >= $class->capacity) {
        $session->addMessage('error', 'This class is full.');
    } elseif (Enrollment::isEnrolled($db, $class_id, $session->getId())) {
        $session->addMessage('error', 'You are already enrolled in this class.');
    } else {
        Enrollment::enroll($db, $class_id, $session->getId());
        $session->addMessage('success', 'You have been enrolled in ' . $class->name . '!');
    }
} elseif ($action === 'cancel') {
    Enrollment::cancel($db, $class_id, $session->getId());
    $session->addMessage('success', 'Your enrollment has been cancelled.');
}

header('Location: /pages/class.php?id=' . $class_id);
exit;
