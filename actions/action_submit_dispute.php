<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }
if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/disputes.php'); exit;
}

require_once('../database/connection.db.php');
require_once('../database/dispute.class.php');

$db       = getDatabaseConnection();
$category = trim($_POST['category']    ?? '');
$desc     = trim($_POST['description'] ?? '');

$result = Dispute::create($db, $session->getId(), $category, $desc);
[$type, $msg] = explode(':', $result, 2);
$session->addMessage($type, $msg);
header('Location: /pages/disputes.php'); exit;