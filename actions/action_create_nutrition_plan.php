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
require_once(__DIR__ . '/../database/user.class.php');

$db      = getDatabaseConnection();
$trainer = Trainer::getTrainerByUserId($db, $session->getId());

if (!$trainer) {
    $session->addMessage('error', 'Trainer profile not found.');
    header('Location: /pages/index.php'); exit;
}

$member_id   = (int)($_POST['member_id']   ?? 0);
$title       = trim($_POST['title']        ?? '');
$description = trim($_POST['description']  ?? '');
$calories    = ($_POST['calories'] ?? '') !== '' ? (int)$_POST['calories'] : null;

if ($title === '' || $member_id === 0) {
    $session->addMessage('error', 'Title and member are required.');
    header('Location: /pages/nutrition_plans.php'); exit;
}

$member = User::getUser($db, $member_id);
if (!$member || $member->type !== 'member') {
    $session->addMessage('error', 'Invalid member selected.');
    header('Location: /pages/nutrition_plans.php'); exit;
}

// Collect non-empty meals
$raw_meals = $_POST['meals'] ?? [];
$meals = [];
foreach ($raw_meals as $m) {
    $name = trim($m['name'] ?? '');
    if ($name === '') continue;
    $meals[] = [
        'name'        => $name,
        'time_of_day' => trim($m['time_of_day'] ?? ''),
        'calories'    => (int)($m['calories'] ?? 0),
        'notes'       => trim($m['notes'] ?? ''),
    ];
}

NutritionPlan::create($db, $trainer->id, $member_id, $title, $description, $calories, $meals);

$session->addMessage('success', 'Nutrition plan created and assigned to ' . $member->name . '.');
header('Location: /pages/nutrition_plans.php');
