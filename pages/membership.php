<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }

require_once('../database/connection.db.php');
require_once('../database/user.class.php');
require_once('../database/membership_plan.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/membership.tpl.php');

$db         = getDatabaseConnection();
$user       = User::getUser($db, $session->getId());
$plans      = MembershipPlan::getAll($db, activeOnly: true);
$current    = $user->plan_id ? MembershipPlan::getById($db, $user->plan_id) : null;
$pending    = MembershipPlan::getPendingRequestForUser($db, $user->id);
$weeklyUsed = MembershipPlan::getEnrollmentsThisWeek($db, $user->id);

drawHeader($session, 'Membership Plans — GymFlow');
drawMembershipPage($user, $plans, $current, $pending, $session, $weeklyUsed);
drawFooter();