<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormsController {

    private $request;

    public function registerUser(Application $app, Request $request, Database $db) {

        $validator = new Validator();
        $imageProcessing = new ImageProcessing();

        $userName = $request->request->get('username');
        $mail = $request->request->get('mail');
        $date = $request->request->get('date');
        $password = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');

        $newUser = new User($userName, $password, $mail, $date, 0, -1);

        //TODO: Validacion del resto de parametros
//        $img = $request->;
        //TODO proces de registre usuari

        if ($validator->validateNewUser($newUser, $confirmPassword)) {
            //Image not null
            if($profileImage != null) {
                //Image validation
                if ($validator->validateProfileImage($profileImage->getClientSize(), $profileImage->getClientOriginalExtension())) {
                    //Save user's profile image
                    $imageProcessing->saveImage($userName, $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());
                    return $app['twig']->render('base.twig',array(
                        'request'=>$request,
                    ));

                } else {
                    //TODO: mostrar error de la imagen des de PHP
                    return $app -> redirect('/register');
                }
            }

            return $app['twig']->render('base.twig',array(
                'request'=>$request,
            ));

        } else {
            //Data error
           return $app -> redirect('/register');
//            return $app['twig']->render('base.twig',array(
//                'request'=>$request,
//            ));
        }




    }

    public function loginUser(Application $app, Request $request, Database $db) {

        var_dump($request->request);

        //TODO proces de logejar usuari


        return $app['twig']->render('base.twig',array(
            'request'=>$request,
        ));

    }

}