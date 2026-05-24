<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');

$id       = (int)($_POST['id'] ?? 0);
$name     = trim($_POST['name'] ?? '');
$type     = trim($_POST['type'] ?? '');
$quantity = (int)($_POST['quantity'] ?? 1);
$in_use   = (int)($_POST['in_use'] ?? 0);

if ($name === '' || $quantity < 1) {
    $session->addMessage('error', 'Name is required and quantity must be at least 1');
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

$in_use = max(0, min($in_use, $quantity));

$db = getDatabaseConnection();

if ($id > 0) {
    $item = Equipment::getById($db, $id);
    if (!$item) {
        $session->addMessage('error', 'Equipment not found');
        die(header('Location: ../pages/admin_equipment.php'));
    }
    $item->name     = $name;
    $item->type     = $type;
    $item->quantity = $quantity;
    $item->in_use   = $in_use;
    $item->save($db);
    $session->addMessage('success', 'Equipment updated');
} else {
    Equipment::create($db, $name, $type, $quantity, $in_use);
    $session->addMessage('success', 'Equipment added');
}

header('Location: ../pages/admin_equipment.php');
?>