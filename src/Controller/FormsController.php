<?php

namespace pwgram\Controller;

session_start();

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class FormsController {

    private $request;



    public function getUserFromForm(Request $request) {


        $userName = $request->request->get('username');
        $mail = $request->request->get('mail');
        $date = $request->request->get('date');
        $password = $request->request->get('password');
        $profileImage = $request->files->get('image-path') != null;

        $user = new User($userName, $mail, $date, 0, false, -1, $password);


        return $user;
    }

    public function checkUserImage(User &$user, Validator $validator, $profileImage) {

        //Image validation
        if ($validator->validateProfileImage($profileImage->getClientSize(), $profileImage->getClientOriginalExtension())) {

            $user->setProfileImage(true);
            return true;
        }

        return false;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function registerUser(Application $app, Request $request, Database $db) {

        $validator = new Validator();
        $imageProcessing = new ImageProcessing();

        $newUser = $this->getUserFromForm($request);

        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');


        if ($validator->validateNewUser($newUser, $confirmPassword)) {
            //Image not null
            if($profileImage != null) {
                //Image validation
                if (!$this->checkUserImage($newUser, $validator, $profileImage)) {

                    //TODO: mostrar error de la imagen desde PHP en twig
                    return $app -> redirect('/register');
                }
            }
        } else {

            //Data error
            //TODO:mostrar errores del formulario des de PHP en twig
           return $app -> redirect('/register');
        }

        //Encrypt user password before insert in database
        $newUser->setPassword(crypt($newUser->getPassword(), '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));

        //All correct, register new user in DB
        $pdoUser = new PdoUserRepository($db);
        $pdoUser->add($newUser);

        //Save User profile image
        $idUser = $pdoUser->getId($newUser->getUsername());
        if($newUser->getProfileImage()) $imageProcessing->saveImage(strval($idUser), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());

        //Send validation email
        $emailManager = new EmailManager();
        //TODO: cambiar mail
        $emailSentOK = $emailManager->sendEmail("albertpv95@icloud.com", $idUser);

        if ($emailSentOK) {
            return $app -> redirect('/validation');
        }
        return $app['twig']->render('error.twig',array(
            'message'=>"Ha sido imposible enviar el mail de verificacion. Vuelva a intentarlo.",
        ));


    }

    /**
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginUser(Application $app, Request $request, Database $db) {

        $userNameOrEmail = $request->request->get('usernameOrMail');
        $password = $request->request->get('password');


        $pdoUser = new PdoUserRepository($db);

        // Get user password from DB
        $dbPassword = $pdoUser->getPassword($userNameOrEmail);

        if ($dbPassword != false) {
            // Compare password from db with password entered
            if (crypt($password, $dbPassword) == $dbPassword) {
                //Password are equals
                //TODO: set coookies and sesion
                $userName = $pdoUser->getUsername($userNameOrEmail);


                $this->setSession($app, $userName, $dbPassword);

                return $app -> redirect('/');
            }
        }
        return $app['twig']->render('error.twig',array(
                'message'=>"El usuario o la contraseña no son correctos",
            ));

    }

    /**
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateUser(Application $app, Request $request, Database $db) {

        $userUpdate = $this->getUserFromForm($request);
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');


        // TODO: Check if user is logged?
        $userName = $app['session']->get('user')['username'];

        $pdo = new PdoUserRepository($db);

        $currentUserName = $pdo->getUsername($userName);
        $userId = $pdo->getId($currentUserName);

        $currentUser = $pdo->get($userId);

        $userUpdate->setEmail($currentUser->getEmail()); // data from db that does not change
        $userUpdate->setId($userId);

        $validator = new Validator();

        if ($validator->validateUserUpdate($currentUser, $userUpdate, $confirmPassword)) {

            //Encrypt user password before insert in database
            $userUpdate->setPassword(crypt($userUpdate->getPassword(), '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));

            //Image not null
            if($profileImage != null) {

                if (!$this->checkUserImage($userUpdate, $validator, $profileImage)) {

                    return $app['twig']->render('error.twig',array(
                        'message'=>"No se han podido aplicar los cambios en el perfil. Imagen no válida.",
                    ));
                }
            }

            $pdo->update($userUpdate); // updates de database user row with the new data
            return $app -> redirect('/');
        }

        return $app['twig']->render('error.twig',array(
            'message'=>"No se han podido aplicar los cambios en el perfil.",
        ));
    }

    public function uploadImage(Application $app, Request $request, Database $db) {
        //TODO: verificar campos de la imagen (tiulo existente, foto existente)
        $title = $request->request->get('img-title');
        $private = $request->request->get('img-private');
         //TODO: redimension
        $pdoUser = new PdoUserRepository($db);
        $idUser = $pdoUser->getId($app['session']->get('user')['username']);


    }

    public function setSession(Application $app, $userName, $dbPassword) {

        // Only one session at the same time
        $app['session']->clear();
        // Save the session
        $app['session']->set('user', array('username' => $userName, 'password' => $dbPassword));
    }


}