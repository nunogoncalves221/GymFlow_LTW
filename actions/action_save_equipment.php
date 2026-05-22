<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');

$id        = (int)($_POST['id'] ?? 0);
$name      = trim($_POST['name'] ?? '');
$type      = trim($_POST['type'] ?? '');
$quantity  = (int)($_POST['quantity'] ?? 0);
$available = (bool)(int)($_POST['available'] ?? 1);

if ($name === '' || $quantity < 0) {
    $session->addMessage('error', 'Name is required and quantity must be non-negative');
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

$db = getDatabaseConnection();

if ($id > 0) {
    $item = Equipment::getById($db, $id);
    if (!$item) {
        $session->addMessage('error', 'Equipment not found');
        die(header('Location: ../pages/admin_equipment.php'));
    }
    $item->name      = $name;
    $item->type      = $type;
    $item->quantity  = $quantity;
    $item->available = $available;
    $item->save($db);
    $session->addMessage('success', 'Equipment updated');
} else {
    Equipment::create($db, $name, $type, $quantity, $available);
    $session->addMessage('success', 'Equipment added');
}

header('Location: ../pages/admin_equipment.php');
?>
