<?php

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Exception\ValidatorException;

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
        if ($validator->validateImage($profileImage->getClientSize(), $profileImage->getClientOriginalExtension())) {

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
    public function registerUser(Application $app, Request $request) {

        $db = Database::getInstance("pwgram");

        $validator = new Validator();
        $imageProcessing = new ImageProcessing();

        $newUser = $this->getUserFromForm($request);

        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');


        if ($validator->validateNewUser($app, $newUser, $confirmPassword)) {
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
        $pdoUser->add($app, $newUser);

        //Save User profile image
        $idUser = $pdoUser->getId($app, $newUser->getUsername());
        if($newUser->getProfileImage()) $imageProcessing->saveProfileImage(strval($idUser), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());

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
    public function loginUser(Application $app, Request $request) {

        $db = Database::getInstance("pwgram");

        $userNameOrEmail = $request->request->get('usernameOrMail');
        $password = $request->request->get('password');


        $pdoUser = new PdoUserRepository($db);

        // Get user password from DB
        $dbPassword = $pdoUser->getPassword($app, $userNameOrEmail);

        if ($dbPassword != false) {
            // Compare password from db with password entered
            if (crypt($password, $dbPassword) == $dbPassword) {
                //Password are equals
                //TODO: set coookies and sesion
                $userName = $pdoUser->getUsername($app, $userNameOrEmail);


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
    public function updateUser(Application $app, Request $request) {

        $db = Database::getInstance("pwgram");

        $userUpdate = $this->getUserFromForm($request);
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');

        $userName = $app['session']->get('user')['username'];

        $pdo = new PdoUserRepository($db);

        $currentUserName = $pdo->getUsername($app, $userName);
        $userId = $pdo->getId($app, $currentUserName);

        $currentUser = $pdo->get($app, $userId);

        $userUpdate->setEmail($currentUser->getEmail()); // data from db that does not change
        $userUpdate->setId($userId);

        $validator = new Validator();

        if ($validator->validateUserUpdate($app, $currentUser, $userUpdate, $confirmPassword)) {

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

            //updates the info of the session
            $this->setSession($app,$userUpdate->getUsername(), $userUpdate->getPassword());
            // updates the database user row with the new data
            $pdo->update($app, $userUpdate);

            return $app -> redirect('/');
        }

        return $app['twig']->render('error.twig',array(
            'message'=>"No se han podido aplicar los cambios en el perfil.",
        ));

    }


    /**
     * Image upload. Verify data form, image characteristics (size, extension...), update BBDD and
     * save the image in two different size: 400x300 and 100x100
     * The users image are saved in upload_img directory inside web/assets/img
     * The users image follow this nomenclature:
     *  - {id-image}100x100.{extension}
     *  - {id-image}400x300.{extension}
     *
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadImage(Application $app, Request $request) {

        $db = Database::getInstance("pwgram");

        //TODO: verificar campos de la imagen (tiulo existente, foto existente)
        $validator = new Validator();

        $title = $request->request->get('img-title');
        $private = $request->request->get('img-private') != null;
        $image = $request->files->get('img-selected');

        // Check if the image accomplish the requirements
        if (!$validator->validateUploadImage($title, $image)) {
            //TODO: error desde php avisando que no se puede subir la imagen seleccionada
            return $app -> redirect('/upload-image');
        }

        // Correct image, save it and update DB

        $pdoUser = new PdoUserRepository($db);
        $idUser = $pdoUser->getId($app, $app['session']->get('user')['username']);

        // Create image entity
        date_default_timezone_set('Europe/Madrid');

        $newImage = new Image($title, date('Y-m-d H:i:s'), $idUser, $private);

        // Save image information in DB image table
        $pdoImage = new PdoImageRepository($db);

        $pdoImage->add($app, $newImage);

        $idImage = $pdoImage->getLastInsertedId($app);


        $imageProcessing = new ImageProcessing();
        $imageProcessing->saveUploadImage($idImage, $image->getClientOriginalExtension(), $image->getRealPath());
        return $app -> redirect('/');
    }

    public function deleteImage(Application $app, $idImage){

        $sessionController = new SessionController();
        if($sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");

            $pdoImage = new PdoImageRepository($db);
            $pdoImage->remove($app, $idImage);

            return $app -> redirect('/user-images');
        }else return $app -> redirect('/login');

    }

    public function editImageForm(Application $app, $idImage){

        $sessionController = new SessionController();
        if($sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");

            //TODO falta coger de la bbdd la imagen y hacer un alter, sera llamar la funcion

            return $app -> redirect('/user-images');

        }else  return $app -> redirect('/login');



    }


    /**
     * @param Application $app
     * @param $userName
     * @param $dbPassword
     */
    public function setSession(Application $app, $userName, $dbPassword) {

        // Only one session at the same time
        $app['session']->clear();
        // Save the session
        $app['session']->set('user', array('username' => $userName, 'password' => $dbPassword));
        $app['session']->start();
    }



}