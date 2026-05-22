<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');
require_once(__DIR__ . '/../database/equipment.class.php');

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$db = getDatabaseConnection();

$stats = [
    'members'   => count(User::getByType($db, 'member')),
    'trainers'  => count(Trainer::getTrainers($db)),
    'classes'   => count(FitnessClass::getClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];

drawHeader($session, 'Admin — GymFlow');
drawAdminNav('dashboard');
drawAdminDashboard($stats);
drawFooter();
?>
