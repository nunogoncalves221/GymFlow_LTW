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

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
    $session->addMessage('error', 'No file selected.');
    header('Location: /pages/profile.php'); exit;
}

$file = $_FILES['profile_picture'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $session->addMessage('error', 'Upload failed. Please try again.');
    header('Location: /pages/profile.php'); exit;
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);

if (!in_array($mime, $allowedMimes, true)) {
    $session->addMessage('error', 'Only JPEG, PNG, GIF, and WebP images are allowed.');
    header('Location: /pages/profile.php'); exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    $session->addMessage('error', 'Image must be smaller than 2 MB.');
    header('Location: /pages/profile.php'); exit;
}

$ext      = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
};
$filename = $user->id . '.' . $ext;
$destDir  = __DIR__ . '/../docs/profile_pictures/uploads/';
$destPath = $destDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    $session->addMessage('error', 'Could not save the image. Please try again.');
    header('Location: /pages/profile.php'); exit;
}

$urlPath = '/docs/profile_pictures/uploads/' . $filename;
$user->saveProfilePicture($db, $urlPath);

$session->addMessage('success', 'Profile picture updated.');
header('Location: /pages/profile.php');
