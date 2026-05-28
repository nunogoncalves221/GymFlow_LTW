<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

header('Content-Type: application/json');

if (!$session->isLoggedIn() || $session->getType() !== 'trainer') {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/nutrition_plan.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');

$db      = getDatabaseConnection();
$trainer = Trainer::getTrainerByUserId($db, $session->getId());
$plan_id = (int)($_GET['plan_id'] ?? 0);

if (!$trainer || $plan_id === 0) {
    echo json_encode(['meals' => []]); exit;
}

$plan = NutritionPlan::getById($db, $plan_id);
if (!$plan || $plan->trainer_id !== $trainer->id) {
    echo json_encode(['error' => 'Not found']); exit;
}

$meals = NutritionMeal::getByPlan($db, $plan_id);
$out   = array_map(fn($m) => [
    'name'        => $m->name,
    'time_of_day' => $m->time_of_day,
    'calories'    => $m->calories,
    'notes'       => $m->notes,
], $meals);

echo json_encode(['meals' => $out]);
