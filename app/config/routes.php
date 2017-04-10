<?php

use pwgram\Controller\homeController;

$app->get('', 'pwgram\\Controller\\HomeController::renderHome');
