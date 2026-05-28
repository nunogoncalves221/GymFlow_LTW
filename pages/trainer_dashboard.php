<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }
if ($session->getType() !== 'trainer') { header('Location: /pages/index.php'); exit; }

require_once('../database/connection.db.php');
require_once('../database/trainer.class.php');
require_once('../database/fitnessclass.class.php');
require_once('../database/enrollment.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/trainer_dashboard.tpl.php');

$db      = getDatabaseConnection();
$trainer = Trainer::getTrainerByUserId($db, $session->getId());
$classes = FitnessClass::getByTrainer($db, $trainer->id);

$rosters = [];
foreach ($classes as $class) {
    $rosters[$class->id] = Enrollment::getEnrolledMembers($db, $class->id);
}

drawHeader($session, 'GymFlow — My Dashboard');
drawTrainerDashboard($trainer, $classes, $rosters, $session);
drawFooter();
