<?php

use pwgram\Controller\homeController;


$app->post('/login/form', 'pwgram\\Controller\\FormsController::loginUser');

$app->post('/register/form', 'pwgram\\Controller\\FormsController::registerUser');

$app->post('/edit_profile/form', 'pwgram\\Controller\\FormsController::updateUser');

$app->post('/uploadImage/form', 'pwgram\\Controller\\FormsController::uploadImage');

$app->post('/new-comment/{id}', 'pwgram\\Controller\\FormsController::addComment'); //???? TODO no esta no?

$app->get('', 'pwgram\\Controller\\RenderController::renderHome');

/*Basics, no logged user*/
$app->get('/login', 'pwgram\\Controller\\RenderController::renderLogin');
$app->get('/register', 'pwgram\\Controller\\RenderController::renderRegistration');

/*Account verification*/
$app->get('/validation', 'pwgram\\Controller\\RenderController::renderValidation');
$app->get('/dovalidation/{id}', 'pwgram\\Controller\\ValidationController::userValidation');

/*Menu routes, logged user*/
$app->get('/logout', 'pwgram\\Controller\\RenderController::logout');
$app->get('/upload-image', 'pwgram\\Controller\\RenderController::renderUploadImage');


$app->get('/notifications', 'pwgram\\Controller\\RenderController::renderHome');

$app->get('/edit-profile', 'pwgram\\Controller\\RenderController::renderEditProfile');

$app->get('/editImage', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/profile', 'pwgram\\Controller\\RenderController::renderHome');

/*Comments*/
$app->get('/new-comment/{id}', 'pwgram\\Controller\\CommentsController::addComment');

/*Like*/
$app->get('/like/{id}', 'pwgram\\Controller\\LikesController::addLike');

/*Image View*/
$app->get('/image-view/{id}', 'pwgram\\Controller\\RenderController::renderImageView');

/*User Profile*/
$app->get('/user-profile/{id}','pwgram\\Controller\\RenderController::renderUserProfile');

$app->get('/user-images/', 'pwgram\\Controller\\RenderController::renderUserImages');

$app->get('/edit-image/{idImage}', 'pwgram\\Controller\\RenderController::editImage');
$app->get('/delete-image/{idImage}', 'pwgram\\Controller\\FormsController::deleteImage');
$app->post('/editImage/form/{idImage}', 'pwgram\\Controller\\FormsController::editImageForm');







