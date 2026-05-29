<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    header('Location: /pages/login.php');
    exit;
}

require_once('../database/connection.db.php');
require_once('../database/equipment.class.php');
require_once('../database/equipment_reservation.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/equipment.tpl.php');

$db        = getDatabaseConnection();
$equipment = Equipment::getAll($db);
$types     = Equipment::getTypes($db);
$is_member = $session->getType() === 'member';

$reservations = $is_member
    ? EquipmentReservation::getUpcomingByUser($db, $session->getId())
    : [];

$filter_type = trim($_GET['type'] ?? '');

$filtered = $filter_type
    ? array_filter($equipment, fn($e) => $e->type === $filter_type)
    : $equipment;

drawHeader($session, 'GymFlow — Equipment');
drawEquipmentPage(array_values($filtered), $types, $filter_type, $session, $is_member, $reservations);
drawFooter();
