<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once('../database/connection.db.php');
require_once('../database/dispute.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/admin.tpl.php');
require_once('../templates/admin_disputes.tpl.php');

$db       = getDatabaseConnection();
$filter   = $_GET['status'] ?? '';
$disputes = Dispute::getAll($db);

if ($filter !== '') {
    $disputes = array_filter($disputes, fn($d) => $d->status === $filter);
    $disputes = array_values($disputes);
}

$counts = [
    'open'      => Dispute::countByStatus($db, 'open'),
    'in_review' => Dispute::countByStatus($db, 'in_review'),
    'resolved'  => Dispute::countByStatus($db, 'resolved'),
];

drawHeader($session, 'Admin — Disputes');
drawAdminNav('disputes');
drawAdminDisputeList($disputes, $counts, $filter, $session);
drawFooter();