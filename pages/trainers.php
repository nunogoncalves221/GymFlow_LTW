<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

require_once('../database/connection.db.php');
require_once('../database/trainer.class.php');
require_once('../database/fitnessclass.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/trainers.tpl.php');

$db       = getDatabaseConnection();
$trainers = Trainer::getTrainers($db);

drawHeader($session, 'GymFlow — Our Trainers');
drawTrainersList($trainers);
drawFooter();