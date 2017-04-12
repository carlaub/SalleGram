<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormsController {

    private $request;

    public function registerUser(Application $app, Request $request, Database $db) {

        var_dump($request->request);

        //TODO proces de registre usuari

        return $app['twig']->render('base.twig',array(
            'request'=>$request,
        ));

    }

    public function loginUser(Application $app, Request $request, Database $db) {

        var_dump($request->request);

        //TODO proces de logejar usuari


        return $app['twig']->render('base.twig',array(
            'request'=>$request,
        ));

    }

}