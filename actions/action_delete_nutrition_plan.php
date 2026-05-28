<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'trainer') {
    header('Location: /pages/login.php'); exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/nutrition_plans.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/nutrition_plan.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');

$db      = getDatabaseConnection();
$trainer = Trainer::getTrainerByUserId($db, $session->getId());
$plan_id = (int)($_POST['plan_id'] ?? 0);

if (!$trainer || $plan_id === 0) {
    header('Location: /pages/nutrition_plans.php'); exit;
}

$plan = NutritionPlan::getById($db, $plan_id);

if (!$plan || $plan->trainer_id !== $trainer->id) {
    $session->addMessage('error', 'Plan not found or access denied.');
    header('Location: /pages/nutrition_plans.php'); exit;
}

NutritionPlan::delete($db, $plan_id);
$session->addMessage('success', 'Nutrition plan deleted.');
header('Location: /pages/nutrition_plans.php');
