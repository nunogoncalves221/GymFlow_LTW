<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$db     = getDatabaseConnection();
$action = $_GET['action'] ?? '';

drawHeader($session, 'Equipment — Admin');
drawAdminNav('equipment');

if (($_POST['action'] ?? '') === 'delete') {
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->addMessage('error', 'Invalid request.');
        header('Location: ../pages/admin_equipment.php'); exit;
    }
    Equipment::delete($db, (int)($_POST['id'] ?? 0));
    $session->addMessage('success', 'Equipment deleted');
    header('Location: ../pages/admin_equipment.php'); exit;
} elseif ($action === 'create') {
    drawEquipmentForm(null, $session);
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $item = Equipment::getById($db, (int)$_GET['id']);
    if ($item) drawEquipmentForm($item, $session);
    else {
        $session->addMessage('error', 'Equipment not found');
        header('Location: ../pages/admin_equipment.php');
    }
} else {
    $equipment = Equipment::getAll($db);
    drawEquipmentList($equipment, $session);
}

drawFooter();
?>
