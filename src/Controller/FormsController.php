<?php

namespace pwgram\Controller;

use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormsController {

    private $request;

    public function registerUser(Application $app, Request $request) {

        var_dump($_REQUEST);
        return $app['twig']->render('base.twig',array(
            'request'=>$request,
        ));

    }
}