<?php

ini_set('display_errors', 1);


require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../app/app.php';
$app['debug'] = true;

require __DIR__.'/../app/config/prod.php';
require __DIR__.'/../app/config/routes.php';


use pwgram\Model\Entity\User;
use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoUserRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Entity\Notification;
use pwgram\Model\AppFormatDate;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoCommentRepository;


date_default_timezone_set('Europe/Madrid');
$db = Database::getInstance("pwgram");

$pdo = new PdoUserRepository($db);

$app->run();

