<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin') {
    header('Location: /pages/login.php'); exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/admin_memberships.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/membership_plan.class.php');

$db           = getDatabaseConnection();
$id           = (int)($_POST['id'] ?? 0);
$name         = trim($_POST['name'] ?? '');
$price        = (float)($_POST['price'] ?? 0);
$description  = trim($_POST['description'] ?? '');
$max_per_week = ($_POST['max_enrollments_per_week'] === '' || $_POST['max_enrollments_per_week'] === '0')
                ? null : (int)$_POST['max_enrollments_per_week'];
$access_level = (int)($_POST['access_level'] ?? 1);
$is_active    = isset($_POST['is_active']) ? 1 : 0;
$features     = array_filter(array_map('trim', explode("\n", trim($_POST['features'] ?? ''))));

if ($name === '') {
    $session->addMessage('error', 'Plan name is required.');
    header('Location: /pages/admin_memberships.php'); exit;
}

if ($id > 0) {
    $plan = MembershipPlan::getById($db, $id);
    if (!$plan) {
        $session->addMessage('error', 'Plan not found.');
        header('Location: /pages/admin_memberships.php'); exit;
    }
    $plan->name                     = $name;
    $plan->price                    = $price;
    $plan->description              = $description;
    $plan->max_enrollments_per_week = $max_per_week;
    $plan->access_level             = $access_level;
    $plan->is_active                = (bool)$is_active;
    $plan->save($db, $features);
    $session->addMessage('success', 'Plan updated successfully.');
} else {
    MembershipPlan::create($db, $name, $price, $description, $max_per_week, $access_level, $features);
    $session->addMessage('success', 'Plan created successfully.');
}

header('Location: /pages/admin_memberships.php');