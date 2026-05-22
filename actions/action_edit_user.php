<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');

$id       = (int)($_POST['id'] ?? 0);
$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $username === '') {
    $session->addMessage('error', 'Name and username are required');
    die(header('Location: ../pages/admin_users.php?action=edit&id=' . $id));
}

$db   = getDatabaseConnection();
$user = User::getUser($db, $id);

if (!$user) {
    $session->addMessage('error', 'User not found');
    die(header('Location: ../pages/admin_users.php'));
}

if (User::usernameExists($db, $username, $id)) {
    $session->addMessage('error', 'Username already taken');
    die(header('Location: ../pages/admin_users.php?action=edit&id=' . $id));
}

$user->name     = $name;
$user->username = $username;
$user->save($db);

if ($password !== '') {
    $db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([sha1($password), $id]);
}

$session->addMessage('success', 'User updated');
header('Location: ../pages/admin_users.php');
?>
