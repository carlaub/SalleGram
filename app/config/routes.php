<?php

use pwgram\Controller\homeController;

$app->get('', 'pwgram\\Controller\\HomeController::renderHome');

$app->get('/login', 'pwgram\\Controller\\HomeController::renderHome');
$app->get('/register', 'pwgram\\Controller\\HomeController::renderHome');
$app->get('/notifications', 'pwgram\\Controller\\HomeController::renderHome');
$app->get('/edit-profile', 'pwgram\\Controller\\HomeController::renderHome');
$app->get('/edit-image', 'pwgram\\Controller\\HomeController::renderHome');
$app->get('/add-image', 'pwgram\\Controller\\HomeController::renderHome');
$app->get('/uplaod-images', 'pwgram\\Controller\\HomeController::renderHome');

$app->get('/profile', 'pwgram\\Controller\\HomeController::renderHome');



