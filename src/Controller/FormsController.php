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

        $validator = new Validator();
        $imageProcessing = new ImageProcessing();

        var_dump($request->files->get('image-path'));
        $userName = $request->request->get('username');
        $mail = $request->request->get('mail');
        $date = $request->request->get('date');
        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');
        //TODO: Validacion del resto de parametros
//        $img = $request->;
        //TODO proces de registre usuari

        //Image validation
        if ($validator->validateProfileImage($profileImage->getClientSize(), $profileImage->getClientOriginalExtension())) {
            //Save image
            $imageProcessing->saveImage($userName, $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());
        }

        //Save user's profile image


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