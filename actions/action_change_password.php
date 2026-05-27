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

$db = getDatabaseConnection();

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password']     ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$user = User::getUserWithPassword($db, User::getUser($db, $session->getId())->username, $current);

if (!$user) {
    $session->addMessage('error', 'Current password is incorrect.');
    header('Location: /pages/profile.php'); exit;
}

if ($new !== $confirm) {
    $session->addMessage('error', 'New passwords do not match.');
    header('Location: /pages/profile.php'); exit;
}

$stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([password_hash($new, PASSWORD_DEFAULT), $session->getId()]);

$session->addMessage('success', 'Password updated.');
header('Location: /pages/profile.php');
