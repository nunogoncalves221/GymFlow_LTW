<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'You must be logged in.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/pages/equipment.php')); exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/pages/equipment.php')); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment_reservation.class.php');

$db             = getDatabaseConnection();
$reservation_id = (int)($_POST['reservation_id'] ?? 0);

$result = EquipmentReservation::stop($db, $reservation_id, $session->getId());
[$type, $text] = explode(':', $result, 2);

$session->addMessage($type, $text);
header('Location: /pages/equipment.php');
