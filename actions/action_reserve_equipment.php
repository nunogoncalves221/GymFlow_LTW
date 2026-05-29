<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'You must be logged in to reserve equipment.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/pages/equipment.php')); exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/pages/equipment.php')); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/equipment_reservation.class.php');

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());

if ($user->type !== 'member') {
    $session->addMessage('error', 'Only members can reserve equipment.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/pages/equipment.php')); exit;
}

$equipment_id = (int)($_POST['equipment_id'] ?? 0);
$day          = trim($_POST['date_day']    ?? '');
$month        = trim($_POST['date_month']  ?? '');
$start_hour   = trim($_POST['start_hour']   ?? '');
$start_minute = trim($_POST['start_minute'] ?? '');
$end_hour     = trim($_POST['end_hour']     ?? '');
$end_minute   = trim($_POST['end_minute']   ?? '');

if (!$equipment_id || !$day || !$month || !$start_hour || !$start_minute || !$end_hour || !$end_minute) {
    $session->addMessage('error', 'Please fill in all fields.');
    header('Location: /pages/equipment.php'); exit;
}

$year = (int)date('Y');
if (!checkdate((int)$month, (int)$day, $year)) {
    $session->addMessage('error', 'Invalid date.');
    header('Location: /pages/equipment.php'); exit;
}
if ((int)$start_hour > 23 || (int)$start_minute > 59 || (int)$end_hour > 23 || (int)$end_minute > 59) {
    $session->addMessage('error', 'Invalid time.');
    header('Location: /pages/equipment.php'); exit;
}

$date       = sprintf('%04d-%02d-%02d', $year, (int)$month, (int)$day);
$start_time = sprintf('%02d:%02d', (int)$start_hour, (int)$start_minute);
$end_time   = sprintf('%02d:%02d', (int)$end_hour,   (int)$end_minute);

$result = EquipmentReservation::create($db, $session->getId(), $equipment_id, $date, $start_time, $end_time);
[$type, $text] = explode(':', $result, 2);

$session->addMessage($type, $text);
header('Location: /pages/equipment.php');
