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

$db     = getDatabaseConnection();
$action = $_POST['action'] ?? '';

if ($action === 'approve') {
    $id = (int)($_POST['request_id'] ?? 0);
    MembershipPlan::approveRequest($db, $id)
        ? $session->addMessage('success', 'Membership request approved.')
        : $session->addMessage('error', 'Request not found.');

} elseif ($action === 'reject') {
    $id = (int)($_POST['request_id'] ?? 0);
    MembershipPlan::rejectRequest($db, $id)
        ? $session->addMessage('success', 'Membership request rejected.')
        : $session->addMessage('error', 'Request not found.');

} elseif ($action === 'set_plan') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $plan_id = ($_POST['plan_id'] === '' || $_POST['plan_id'] === '0') ? null : (int)$_POST['plan_id'];
    MembershipPlan::setUserPlan($db, $user_id, $plan_id);
    $session->addMessage('success', 'Membership plan updated.');

} else {
    $session->addMessage('error', 'Unknown action.');
}

header('Location: /pages/admin_memberships.php');