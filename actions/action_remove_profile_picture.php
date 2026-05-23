<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());

if ($user->profile_picture && str_starts_with($user->profile_picture, '/docs/profile_pictures/uploads/')) {
    $filePath = __DIR__ . '/..' . $user->profile_picture;
    if (file_exists($filePath)) unlink($filePath);
}

$user->saveProfilePicture($db, '/docs/profile_pictures/default.jpg');

$session->addMessage('success', 'Profile picture removed successfully.');
header('Location: /pages/profile.php');
