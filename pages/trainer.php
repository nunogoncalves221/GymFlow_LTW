<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

require_once('../database/connection.db.php');
require_once('../database/trainer.class.php');
require_once('../database/fitnessclass.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/trainer.tpl.php');

$db = getDatabaseConnection();

$id      = (int)($_GET['id'] ?? 0);
$trainer = Trainer::getTrainer($db, $id);
if (!$trainer) { http_response_code(404); exit('Trainer not found.'); }

$classes = FitnessClass::getByTrainer($db, $id);

drawHeader($session, 'GymFlow — ' . $trainer->name);
drawTrainerProfile($trainer, $classes);
drawFooter();
