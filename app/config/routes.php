<?php

use pwgram\Controller\homeController;

$app->get('', 'pwgram\\Controller\\RenderController::renderHome');

/*Basics, no logged user*/
$app->get('/login', 'pwgram\\Controller\\RenderController::renderLogin');
$app->get('/register', 'pwgram\\Controller\\RenderController::renderRegistration');

/*Account verification*/
$app->get('/validation/{id}', 'pwgram\\Controller\\RenderController::renderValidation');
$app->get('/dovalidation/{id}', 'pwgram\\Controller\\ValidationController::userValidation');

/*Menu routes, loggef user*/
$app->get('/notifications', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/edit-profile', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/edit-image', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/add-image', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/upload-images', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/profile', 'pwgram\\Controller\\RenderController::renderHome');



