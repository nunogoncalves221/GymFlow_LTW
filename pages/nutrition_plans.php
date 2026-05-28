<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }
$type = $session->getType();
if (!in_array($type, ['trainer', 'member'])) { header('Location: /pages/index.php'); exit; }

require_once('../database/connection.db.php');
require_once('../database/nutrition_plan.class.php');
require_once('../database/trainer.class.php');
require_once('../database/user.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/nutrition_plans.tpl.php');

$db = getDatabaseConnection();

if ($type === 'trainer') {
    $trainer = Trainer::getTrainerByUserId($db, $session->getId());
    if (!$trainer) { header('Location: /pages/index.php'); exit; }
    $plans   = NutritionPlan::getByTrainer($db, $trainer->id);
    $members = User::getByType($db, 'member');

    drawHeader($session, 'GymFlow — Nutrition Plans');
    drawTrainerNutritionPlans($trainer, $plans, $members, $session);
    drawFooter();
} else {
    $plans = NutritionPlan::getByMember($db, $session->getId());
    drawHeader($session, 'GymFlow — My Nutrition Plans');
    drawMemberNutritionPlans($plans, $session, $db);
    drawFooter();
}
