<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) {
    header('Location: /pages/login.php'); exit;
}

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/membership.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/membership_plan.class.php');

$db      = getDatabaseConnection();
$plan_id = (int)($_POST['plan_id'] ?? 0);
$plan    = MembershipPlan::getById($db, $plan_id);

if (!$plan || !$plan->is_active) {
    $session->addMessage('error', 'Invalid plan selected.');
    header('Location: /pages/membership.php'); exit;
}

MembershipPlan::createRequest($db, $session->getId(), $plan_id);
$session->addMessage('success', 'Your request to join the ' . $plan->name . ' plan has been submitted. An admin will review it shortly.');
header('Location: /pages/membership.php');