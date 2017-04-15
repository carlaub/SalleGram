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

        if ($validator->validateNewUser($newUser, $confirmPassword)) {
            //Image not null
            if($profileImage != null) {
                //Image validation
                if ($validator->validateProfileImage($profileImage->getClientSize(), $profileImage->getClientOriginalExtension())) {
                    $newUser->setProfileImage(true);


                } else {
                    //TODO: mostrar error de la imagen desde PHP en twig
                    return $app -> redirect('/register');
                }
            }
        } else {
            //Data error
            //TODO:mostrar errores del formulario des de PHP en twig
           return $app -> redirect('/register');
        }

        //All correct, register new user in DB
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);
        $pdoUser->add($newUser);

        //Save User profile image
        $idUser = $pdoUser->getId($userName);
        if($newUser->getProfileImage())$imageProcessing->saveImage(strval($idUser), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());

        //Send validation email
        $emailManager = new EmailManager();
        $emailManager->sendEmail("carlaurreablazquez@gmail.com", $idUser);

//        return $app['twig']->render('base.twig',array(
//            'request'=>$request,
//        ));
        return $app -> redirect('/validation/'.$idUser);

    }

    public function loginUser(Application $app, Request $request, Database $db) {

        var_dump($request->request);

        //TODO proces de logejar usuari


        return $app['twig']->render('base.twig',array(
            'request'=>$request,
        ));

    }

}