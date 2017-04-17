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



$app = new Application();

$app['app.name'] = 'PWGram';


$app->post('/register/form', function (Application $app, Request $request) {
    $db = Database::getInstance("pwgram");

    $formsController= new FormsController();
    return $formsController->registerUser($app, $request, $db);
});

$app->post('/login/form', function (Application $app, Request $request) {
    $db = Database::getInstance("pwgram");

    $formsController= new FormsController();
    return $formsController->loginUser($app, $request, $db);
});

$app->post('/edit_profile/form', function (Application $app, Request $request) {
    $db = Database::getInstance("pwgram");

    $formsController= new FormsController();
    return $formsController->updateUser($app, $request, $db);
});

$app->post('/uploadImage/form', function (Application $app, Request $request) {
    $db = Database::getInstance("pwgram");

    $formsController= new FormsController();
    return $formsController->uploadImage($app, $request, $db);
});

$app->get('/new-comment/{id}', function (Application $app, Request $request) {

    $imageId = $request->get("id");
    $comment = $request->get('text');

    $commentsController = new CommentsController();
    return $commentsController->addComment($app, $imageId, $comment);
});


return $app;
