<?php

use pwgram\Controller\homeController;

$app->get('', 'pwgram\\Controller\\RenderController::renderHome');

/*Basics, no logged user*/
$app->get('/login', 'pwgram\\Controller\\RenderController::renderLogin');
$app->get('/register', 'pwgram\\Controller\\RenderController::renderRegistration');

/*Account verification*/
$app->get('/validation', 'pwgram\\Controller\\RenderController::renderValidation');
$app->get('/dovalidation/{id}', 'pwgram\\Controller\\ValidationController::userValidation');

/*Menu routes, loggef user*/
$app->get('/logout', 'pwgram\\Controller\\RenderController::logout');
$app->get('/upload-image', 'pwgram\\Controller\\RenderController::renderUploadImage');


$app->get('/notifications', 'pwgram\\Controller\\RenderController::renderHome');

$app->get('/edit-profile', 'pwgram\\Controller\\RenderController::renderEditProfile');

$app->get('/editImage', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/profile', 'pwgram\\Controller\\RenderController::renderHome');

/*Comments*/

$app->get('/new-comment/{id}', 'pwgram\\Controller\\CommentsController::addComment');


