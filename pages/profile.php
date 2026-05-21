<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }

require_once('../database/connection.db.php');
require_once('../database/user.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/profile.tpl.php');
require_once('../database/trainer.class.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    $session->logout();
    header('Location: /pages/index.php'); exit;
}

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());
$trainer = $user->type === 'trainer' ? Trainer::getTrainerByUserId($db, $user->id) : null;

drawHeader($session, 'GymFlow — My Profile');
drawProfileForm($user, $trainer);
drawFooter();
