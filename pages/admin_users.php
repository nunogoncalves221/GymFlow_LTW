<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn() || $session->getType() !== 'admin')
    die(header('Location: /pages/login.php'));

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');

require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$db     = getDatabaseConnection();
$action = $_GET['action'] ?? '';

drawHeader($session, 'Users — Admin');
drawAdminNav('users');

if ($action === 'create_member' || $action === 'create_trainer') {
    $type = $action === 'create_trainer' ? 'trainer' : 'member';
    drawCreateUserForm($type);
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $user = User::getUser($db, (int)$_GET['id']);
    if ($user) drawEditUserForm($user);
    else {
        $session->addMessage('error', 'User not found');
        header('Location: ../pages/admin_users.php');
    }
} elseif ($action === 'elevate' && isset($_GET['id'])) {
    $user = User::getUser($db, (int)$_GET['id']);
    if ($user && $user->type !== 'admin') {
        $user->setType($db, 'admin');
        $session->addMessage('success', h($user->name) . ' elevated to admin');
    }
    header('Location: ../pages/admin_users.php');
} elseif ($action === 'activate' && isset($_GET['id'])) {
    $user = User::getUser($db, (int)$_GET['id']);
    if ($user && $user->type === 'deactivated') {
        $trainer = Trainer::getTrainerByUserId($db, $user->id);
        $user->setType($db, $trainer ? 'trainer' : 'member');
        $session->addMessage('success', 'Account reactivated');
    }
    header('Location: ../pages/admin_users.php');
} elseif ($action === 'deactivate' && isset($_GET['id'])) {
    $user = User::getUser($db, (int)$_GET['id']);
    if ($user && in_array($user->type, ['member', 'trainer'])) {
        $user->setType($db, 'deactivated');
        $session->addMessage('success', 'Account deactivated');
    }
    header('Location: ../pages/admin_users.php');
} else {
    $typeFilter = $_GET['type'] ?? '';
    $users = $typeFilter ? User::getByType($db, $typeFilter) : User::getAll($db);
    drawUserList($users, $typeFilter);
}

drawFooter();
?>
