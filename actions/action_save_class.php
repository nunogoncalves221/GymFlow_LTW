<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/admin_classes.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');

$id          = (int)($_POST['id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$type        = trim($_POST['type'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration    = (int)($_POST['duration_min'] ?? 0);
$capacity    = (int)($_POST['capacity'] ?? 0);
$trainer_id  = ($_POST['trainer_id'] !== '') ? (int)$_POST['trainer_id'] : null;
$image       = trim($_POST['image'] ?? '') ?: null;
$day         = $_POST['day_of_week'] ?? '';
$time        = $_POST['time'] ?? '';

$validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

if ($name === '' || $type === '' || $duration <= 0 || $capacity <= 0 || !in_array($day, $validDays) || $time === '') {
    $session->addMessage('error', 'Please fill in all required fields');
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

$db = getDatabaseConnection();

if ($id > 0) {
    $class = FitnessClass::getClass($db, $id);
    if (!$class) {
        $session->addMessage('error', 'Class not found');
        die(header('Location: ../pages/admin_classes.php'));
    }
    $class->name        = $name;
    $class->type        = $type;
    $class->description = $description;
    $class->duration_min = $duration;
    $class->capacity    = $capacity;
    $class->trainer_id  = $trainer_id;
    $class->image       = $image;
    $class->day_of_week = $day;
    $class->time        = $time;
    $class->save($db);
    $session->addMessage('success', 'Class updated');
} else {
    FitnessClass::create($db, $name, $type, $description, $duration, $capacity, $trainer_id, $image, $day, $time);
    $session->addMessage('success', 'Class created');
}

header('Location: ../pages/admin_classes.php');
?>
