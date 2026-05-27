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

$postAction = $_POST['action'] ?? '';

if ($postAction === 'elevate' || $postAction === 'deactivate' || $postAction === 'activate') {
    if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->addMessage('error', 'Invalid request.');
        header('Location: ../pages/admin_users.php'); exit;
    }
    $user = User::getUser($db, (int)($_POST['id'] ?? 0));
    if ($postAction === 'elevate' && $user && $user->type !== 'admin') {
        $user->setType($db, 'admin');
        $session->addMessage('success', h($user->name) . ' elevated to admin');
    } elseif ($postAction === 'deactivate' && $user && in_array($user->type, ['member', 'trainer'])) {
        $user->setType($db, 'deactivated');
        $session->addMessage('success', 'Account deactivated');
    } elseif ($postAction === 'activate' && $user && $user->type === 'deactivated') {
        $trainer = Trainer::getTrainerByUserId($db, $user->id);
        $user->setType($db, $trainer ? 'trainer' : 'member');
        $session->addMessage('success', 'Account reactivated');
    }
    header('Location: ../pages/admin_users.php'); exit;
} elseif ($action === 'create_member' || $action === 'create_trainer') {
    $type = $action === 'create_trainer' ? 'trainer' : 'member';
    drawCreateUserForm($type, $session);
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $user = User::getUser($db, (int)$_GET['id']);
    if ($user) drawEditUserForm($user, $session);
    else {
        $session->addMessage('error', 'User not found');
        header('Location: ../pages/admin_users.php');
    }
} else {
    $typeFilter = $_GET['type'] ?? '';
    $users = $typeFilter ? User::getByType($db, $typeFilter) : User::getAll($db);
    drawUserList($users, $session, $typeFilter);
}

drawFooter();
?>
