<?php

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class FormsUserController
{

    private $request;

    private $sessionController;


    public function __construct()
    {
        $this->sessionController = new SessionController();
    }


    public function getUserFromForm(Request $request)
    {


        $userName = $request->request->get('username');
        $mail = $request->request->get('mail');
        $date = $request->request->get('date');
        $password = $request->request->get('password');
        $profileImage = $request->files->get('image-path') != null;

        $user = new User($userName, $mail, $date, 0, false, -1, $password);


        return $user;
    }


    /**
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function registerUser(Application $app, Request $request)
    {

        $db = Database::getInstance("pwgram");

        $validator = new Validator();
        $imageProcessing = new ImageProcessing();

        $newUser = $this->getUserFromForm($request);
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');


        $errors = $validator->validateNewUser($app, $newUser, $confirmPassword);

        if ($profileImage != null) {
            //Image validation
            $this->checkUserImage($newUser, $validator, $profileImage, $errors);
        }

        if ($validator->haveErrors($errors)) {
            $renderController = new RenderController();
            return $renderController->renderRegistration($app, $errors, $newUser);
        }

        //Encrypt user password before insert in database
        $newUser->setPassword(crypt($newUser->getPassword(), '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));

        //All correct, register new user in DB
        $pdoUser = new PdoUserRepository($db);
        $pdoUser->add($app, $newUser);

        //Save User profile image
        $idUser = $pdoUser->getId($app, $newUser->getUsername());
        if ($newUser->getProfileImage()) $imageProcessing->saveProfileImage(strval($idUser), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());

        //Send validation email
        $emailManager = new EmailManager();

        //TODO: cambiar mail
        $emailSentOK = $emailManager->sendEmail("albertpv95@icloud.com", $idUser);

        if ($emailSentOK) {
            return $app->redirect('/validation');
        }
        return $app['twig']->render('error.twig', array(
            'message' => "Ha sido imposible enviar el mail de verificacion. Vuelva a intentarlo.",
        ));


    }

    /**
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginUser(Application $app, Request $request)
    {

        $error = new FormError();

        $sessionController = new SessionController();

        $db = Database::getInstance("pwgram");

        $userNameOrEmail = $request->request->get('usernameOrMail');
        $password = $request->request->get('password');


        $pdoUser = new PdoUserRepository($db);

        //cuenta no activada
        $dbPassword = $pdoUser->getPassword($app, $userNameOrEmail);

        //var_dump($pdoUser->validateUserLogin($app, $userNameOrEmail, $password));


        // Get user password from DB
        if ($dbPassword != false) {
            // Compare password from db with password entered
            if (crypt($password, $dbPassword) == $dbPassword) {
                //Password are equals

                if (!$pdoUser->validateUserLogin($app, $userNameOrEmail, $password)) {
                    $error->setActiveError(true);

                    $renderController = new RenderController();
                    return $renderController->renderLogin($app, $error);
                }else{
                    $error->setActiveError(false);
                }

                $userName = $pdoUser->getUsername($app, $userNameOrEmail);
                $userId = $pdoUser->getId($app, $userName);


                $sessionController->setSession($app, $userId);
                return $app->redirect('/');
            }
        }
        $error->setUserOrPasswordError(true);

        $renderController = new RenderController();
        return $renderController->renderLogin($app, $error);

    }

    /**
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateUser(Application $app, Request $request)
    {

        $db = Database::getInstance("pwgram");
        $sessionController = new SessionController();
        $pdo = new PdoUserRepository($db);


        $userUpdate = $this->getUserFromForm($request);
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');


        $userId = $this->sessionController->getSessionUserId($app);
        $currentUser = $pdo->get($app, $userId);

        $userUpdate->setEmail($currentUser->getEmail()); // data from db that does not change
        $userUpdate->setId($userId);

        $validator = new Validator();

        $errors = $validator->validateUserUpdate($app, $currentUser, $userUpdate, $confirmPassword);


        if ($profileImage != null) {
            //Image validation
            $this->checkUserImage($userUpdate, $validator, $profileImage, $errors);
        }

        if ($validator->haveErrors($errors)) {
            $renderController = new RenderController();
            return $renderController->renderEditProfile($app, $errors);
        }

        //Encrypt user password before insert in database
        $userUpdate->setPassword(crypt($userUpdate->getPassword(), '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));
        //Image not null
        $errors = new FormError();
        if ($profileImage != null) {
            // Delete previous Image
            unlink('../web/assets/img/profile_img/' . $currentUser->getId() . '.jpg');
            $imageProcessing = new ImageProcessing();
            $imageProcessing->saveProfileImage(strval($currentUser->getId()), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());
            $userUpdate->setProfileImage(true);
        } else {
            $userUpdate->setProfileImage(false);
        }

        //updates the info of the session
        $sessionController->setSession($app, $userUpdate->getId());
        // updates the database user row with the new data
        $pdo->update($app, $userUpdate);

        return $app->redirect('/');

    }


}
