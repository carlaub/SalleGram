<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

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

        var_dump(crypt($password, '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));
        //Encrypt user password before insert in database
        $newUser->setPassword(crypt($password, '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));

        //All correct, register new user in DB
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);
        $pdoUser->add($newUser);

        //Save User profile image
        $idUser = $pdoUser->getId($userName);
        if($newUser->getProfileImage())$imageProcessing->saveImage(strval($idUser), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());

        //Send validation email
        $emailManager = new EmailManager();
        $emailSentOK = $emailManager->sendEmail("carlaurreablazquez@gmail.com", $idUser);

        if ($emailSentOK) {
            return $app -> redirect('/validation');
        }
        return $app['twig']->render('error.twig',array(
            'message'=>"Ha sido imposible enviar el mail de verificacion. Vuelva a intentarlo.",
        ));


    }

    public function loginUser(Application $app, Request $request, Database $db) {

        $userNameOrEmail = $request->request->get('usernameOrMail');
        $password = $request->request->get('password');

        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);

        // Get user password from DB
        $dbPassword = $pdoUser->getPassword($userNameOrEmail);



        if ($dbPassword != false) {
            // Compare password from db with password entered
            if (crypt($password, $dbPassword) == $dbPassword) {
                //Password are equals
                //TODO: set coookies and sesion
                $this->setSession($app, $userNameOrEmail, $dbPassword);

                return $app -> redirect('/');

            }
        }
        return $app['twig']->render('error.twig',array(
                'message'=>"El usuario o la contraseña no son correctos",
            ));

    }

    public function setSession(Application $app, $userNameOrEmail, $dbPassword) {
        // Only one session at the same time
        $app['session']->clear();
        // Save the session
        $app['session']->set('user', array('username' => $userNameOrEmail, 'password' => $dbPassword));
    }

}