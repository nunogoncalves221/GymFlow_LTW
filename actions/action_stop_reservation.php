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

$stmt = $db->prepare('SELECT equipment_id FROM equipment_reservations WHERE id = ? AND user_id = ?');
$stmt->execute([$reservation_id, $session->getId()]);
$res_row = $stmt->fetch();

$result = EquipmentReservation::stop($db, $reservation_id, $session->getId());
[$type, $text] = explode(':', $result, 2);

if ($type === 'success' && $res_row) {
    $db->prepare('UPDATE equipment SET in_use = CASE WHEN in_use > 0 THEN in_use - 1 ELSE 0 END WHERE id = ?')
       ->execute([$res_row['equipment_id']]);
}

$session->addMessage($type, $text);
header('Location: /pages/equipment.php');
