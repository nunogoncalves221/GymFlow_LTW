<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
$session = new Session();

if (!$session->isLoggedIn()) { header('Location: /pages/login.php'); exit; }
if ($session->getType() !== 'trainer') { header('Location: /pages/index.php'); exit; }

if (!$session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $session->addMessage('error', 'Invalid request.');
    header('Location: /pages/profile.php'); exit;
}

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/trainer.class.php');

$db = getDatabaseConnection();

$trainer = Trainer::getTrainerByUserId($db, $session->getId());

$trainer->bio             = $_POST['bio'];
$trainer->specializations = $_POST['specializations'];
$trainer->certifications  = $_POST['certifications'];

$trainer->save($db);

$session->addMessage('success', 'Trainer profile updated!');
header('Location: /pages/profile.php');
exit;
