<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }
if ($session->getType() !== 'trainer') { header('Location: /pages/index.php'); exit; }

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/trainer_dashboard.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');

$db       = getDatabaseConnection();
$class_id = (int)($_POST['class_id'] ?? 0);
$new_day  = $_POST['day'] ?? '';
$new_time = $_POST['time'] ?? '';

$valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if (!in_array($new_day, $valid_days) || !preg_match('/^\d{2}:\d{2}$/', $new_time)) {
    $session->addMessage('error', 'Invalid day or time.');
    header('Location: /pages/trainer_dashboard.php'); exit;
}

$class = FitnessClass::getClass($db, $class_id);
if (!$class) {
    $session->addMessage('error', 'Class not found.');
    header('Location: /pages/trainer_dashboard.php'); exit;
}

$trainer = Trainer::getTrainerByUserId($db, $session->getId());
if (!$trainer || $class->trainer_id !== $trainer->id) {
    $session->addMessage('error', 'You can only reschedule your own classes.');
    header('Location: /pages/trainer_dashboard.php'); exit;
}

[$h, $m]   = array_map('intval', explode(':', $new_time));
$start_min = $h * 60 + $m;
$end_min   = $start_min + $class->duration_min;

if ($start_min < 7 * 60 || $end_min > 22 * 60) {
    $session->addMessage('error', 'Classes must start at or after 07:00 and end by 22:00.');
    header('Location: /pages/trainer_dashboard.php'); exit;
}

$others = FitnessClass::getByTrainerAndDay($db, $trainer->id, $new_day, $class_id);
foreach ($others as $other) {
    [$oh, $om]   = array_map('intval', explode(':', $other->time));
    $other_start = $oh * 60 + $om;
    $other_end   = $other_start + $other->duration_min;
    if ($start_min < $other_end && $end_min > $other_start) {
        $session->addMessage('error', 'Time overlaps with "' . $other->name . '" at ' . $other->time . ' on ' . $new_day . '.');
        header('Location: /pages/trainer_dashboard.php'); exit;
    }
}

FitnessClass::reschedule($db, $class_id, $new_day, $new_time);
$session->addMessage('success', $class->name . ' rescheduled to ' . $new_day . ' at ' . $new_time . '.');
header('Location: /pages/trainer_dashboard.php');
exit;
