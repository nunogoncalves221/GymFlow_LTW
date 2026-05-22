<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

require_once('../database/connection.db.php');
require_once('../database/fitnessclass.class.php');
require_once('../database/trainer.class.php');
require_once('../database/review.class.php');
require_once('../database/enrollment.class.php');
require_once('../templates/common.tpl.php');
require_once('../templates/class.tpl.php');

$db = getDatabaseConnection();

$id    = (int)($_GET['id'] ?? 0);
$class = FitnessClass::getClass($db, $id);
if (!$class) { http_response_code(404); exit('Class not found.'); }

$trainer        = $class->trainer_id ? Trainer::getTrainer($db, $class->trainer_id) : null;
$reviews        = Review::getByClass($db, $id);
$enrolled_count = FitnessClass::getEnrolledCount($db, $id);
$is_enrolled    = $session->isLoggedIn() ? FitnessClass::isEnrolled($db, $session->getId(), $id) : false;
$has_reviewed   = $session->isLoggedIn() ? Review::hasReviewed($db, $session->getId(), $id) : false;

drawHeader($session, 'GymFlow — ' . $class->name);
drawClassDetail($class, $trainer, $reviews, $session, $enrolled_count, $is_enrolled, $has_reviewed);
drawFooter();
