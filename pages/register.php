<?php
declare(strict_types=1);

require_once('../utils/session.php');
$session = new Session();

if ($session->isLoggedIn()) { header('Location: /pages/index.php'); exit; }

require_once('../database/connection.db.php');
require_once('../templates/common.tpl.php');
require_once('../templates/register.tpl.php');

drawHeader($session, 'GymFlow — Register');
drawRegisterForm($session);
drawFooter();
