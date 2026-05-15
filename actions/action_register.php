<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');

$db = getDatabaseConnection();

$name     = trim($_POST['name']             ?? '');
$username = trim($_POST['username']         ?? '');
$password =      $_POST['password']         ?? '';
$confirm  =      $_POST['confirm_password'] ?? '';

if ($password !== $confirm) {
    $session->addMessage('error', 'Passwords do not match.');
    header('Location: /pages/register.php'); exit;
}

$ok = User::register($db, $name, $username, $password);

if ($ok) {
    $user = User::getUserWithPassword($db, $username, $password);
    $session->setId($user->id);
    $session->setName($user->name);
    $session->addMessage('success', 'Welcome, ' . $user->name . '!');
    header('Location: /pages/index.php');
} else {
    $session->addMessage('error', 'Username already taken.');
    header('Location: /pages/register.php');
}
