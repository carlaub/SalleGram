<?php

use Silex\Application;
use pwgram\Controller\homeController;
use pwgram\Controller\formsController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use pwgram\lib\Database\Database;
use pwgram\Controller\CommentsController;
use Doctrine\DBAL\DriverManager;


$app = new Application();

$app['app.name'] = 'PWGram';

return $app;
