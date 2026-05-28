<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }

require_once('../database/connection.db.php');
require_once('../database/dispute.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/disputes.tpl.php');

$db       = getDatabaseConnection();
$disputes = Dispute::getByUser($db, $session->getId());

drawHeader($session, 'GymFlow — My Reports');
drawDisputesPage($disputes, $session);
drawFooter();