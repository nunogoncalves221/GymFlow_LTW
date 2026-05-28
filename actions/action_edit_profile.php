<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/profile.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());

$user->name     = trim($_POST['name']     ?? '');
$user->username = trim($_POST['username'] ?? '');

$user->save($db);

$session->setName($user->name);
$session->addMessage('success', 'Profile updated.');

header('Location: /pages/profile.php');
