<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

require_once('../database/connection.db.php');
require_once('../database/fitnessclass.class.php');
require_once('../database/equipment.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/class.tpl.php');

$db = getDatabaseConnection();

$filter_type    = trim($_GET['type']    ?? '');
$filter_day     = trim($_GET['day']     ?? '');
$filter_trainer = trim($_GET['trainer'] ?? '');
$filter_time    = trim($_GET['time']    ?? '');

$classes   = FitnessClass::getClasses($db, $filter_type, $filter_day, $filter_trainer, $filter_time);
$types     = FitnessClass::getTypes($db);
$trainers  = FitnessClass::getTrainersForFilter($db);
$equipment = Equipment::getAll($db);

$enrollment_counts = [];
foreach ($classes as $c) {
    $enrollment_counts[$c->id] = FitnessClass::getEnrolledCount($db, $c->id);
}

drawHeader($session, 'GymFlow — Classes');
drawHeroBanner();
drawTrainersBanner($session);
drawEquipmentBanner($equipment, $session);
drawNutritionBanner($session);
drawClassList($classes, $types, $trainers, $filter_type, $filter_day, $filter_trainer, $filter_time, $enrollment_counts);
drawPricingSection();
drawFooter();