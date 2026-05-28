<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/membership_plan.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');
require_once(__DIR__ . '/../templates/admin_memberships.tpl.php');

$db     = getDatabaseConnection();
$action = $_GET['action'] ?? '';

drawHeader($session, 'Memberships — Admin');
drawAdminNav('memberships');

if ($action === 'edit' && isset($_GET['id'])) {
    $plan = MembershipPlan::getById($db, (int)$_GET['id']);
    if ($plan) drawPlanForm($plan, $session);
    else { $session->addMessage('error', 'Plan not found.'); header('Location: /pages/admin_memberships.php'); }

} elseif ($action === 'create') {
    drawPlanForm(null, $session);

} else {
    $plans   = MembershipPlan::getAll($db);
    $pending = MembershipPlan::getPendingRequests($db);
    $members = User::getByType($db, 'member');
    drawAdminMembershipsPage($plans, $pending, $members, $session);
}

drawFooter();