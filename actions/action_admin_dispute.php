<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin') {
    header('Location: /pages/login.php'); exit;
}
if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/admin_disputes.php'); exit;
}

require_once('../database/connection.db.php');
require_once('../database/dispute.class.php');

$db       = getDatabaseConnection();
$id       = (int)($_POST['id']            ?? 0);
$response = trim($_POST['admin_response'] ?? '');
$status   = trim($_POST['status']         ?? '');

$result = Dispute::respond($db, $id, $response, $status);
[$type, $msg] = explode(':', $result, 2);
$session->addMessage($type, $msg);
header('Location: /pages/admin_disputes.php'); exit;