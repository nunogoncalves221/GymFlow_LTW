<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$db     = getDatabaseConnection();
$action = $_GET['action'] ?? '';

drawHeader($session, 'Classes — Admin');
drawAdminNav('classes');

if ($action === 'create') {
    $trainers = Trainer::getTrainers($db);
    drawClassForm(null, $trainers);
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $class    = FitnessClass::getClass($db, (int)$_GET['id']);
    $trainers = Trainer::getTrainers($db);
    if ($class) drawClassForm($class, $trainers);
    else {
        $session->addMessage('error', 'Class not found');
        header('Location: ../pages/admin_classes.php');
    }
} elseif ($action === 'delete' && isset($_GET['id'])) {
    FitnessClass::delete($db, (int)$_GET['id']);
    $session->addMessage('success', 'Class deleted');
    header('Location: ../pages/admin_classes.php');
} else {
    $classes = FitnessClass::getClasses($db);
    drawClassList($classes);
}

drawFooter();
?>
