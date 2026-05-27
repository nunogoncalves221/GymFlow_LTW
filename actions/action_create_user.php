<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/admin_users.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');

$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$type     = $_POST['type'] ?? 'member';

if (!in_array($type, ['member', 'trainer', 'admin'])) $type = 'member';

if ($name === '' || $username === '' || $password === '') {
    $session->addMessage('error', 'All fields are required');
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

$db = getDatabaseConnection();

if (User::usernameExists($db, $username)) {
    $session->addMessage('error', 'Username already taken');
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

$ok = User::createWithType($db, $name, $username, $password, $type);

if (!$ok) {
    $session->addMessage('error', 'Could not create user');
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

if ($type === 'trainer') {
    $userId = (int)$db->lastInsertId();
    Trainer::create(
        $db,
        $name,
        trim($_POST['bio'] ?? ''),
        trim($_POST['specializations'] ?? ''),
        trim($_POST['certifications'] ?? ''),
        $userId
    );
}

$session->addMessage('success', ucfirst($type) . ' "' . $name . '" created');
header('Location: ../pages/admin_users.php');
?>
