<?php

namespace pwgram\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class homeController {

    public function renderHome(Application $app, Request $request){
        return $app['twig']->render('home.twig');

    }
}