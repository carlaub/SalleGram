<?php

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use pwgram\Model\Services\PdoMapper;

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

        $validator          = new Validator();
        $imageProcessing    = new ImageProcessing();

        $newUser = $this->getUserFromForm($request);
        $confirmPassword = $request->request->get('confirm-password');
        $profileImage = $request->files->get('image-path');


        $errors = $validator->validateNewUser($app, $newUser, $confirmPassword);

        if ($profileImage != null) {
            //Image validation
            $formsImageController = new FormsImageController($app);
            $formsImageController->checkUserImage($newUser, $validator, $profileImage, $errors);
        }

        if ($validator->haveErrors($errors)) {
            $renderController = new RenderController();
            return $renderController->renderRegistration($app, $errors, $newUser);
        }

        //Encrypt user password before insert in database
        $newUser->setPassword(crypt($newUser->getPassword(), '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));

        //All correct, register new user in DB
        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);
        $pdoUser->add($newUser);

        //Save User profile image
        $idUser = $pdoUser->getId($newUser->getUsername());
        if ($newUser->getProfileImage()) $imageProcessing->saveProfileImage(strval($idUser), $profileImage->getClientOriginalExtension(), $profileImage->getRealPath());

        //Send validation email
        $emailManager = new EmailManager();


        $emailSentOK = $emailManager->sendEmail($newUser->getEmail(), $idUser);

        if ($emailSentOK) {

            $app['monolog']->info(sprintf("User registered with id '%d', name '%s' and email '%s'", $idUser, $newUser->getUsername(), $newUser->getEmail()));
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

        $userNameOrEmail    = $request->request->get('usernameOrMail');
        $password           = $request->request->get('password');


        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);

        //cuenta no activada
        $dbPassword = $pdoUser->getPassword($userNameOrEmail);

        //var_dump($pdoUser->validateUserLogin($app, $userNameOrEmail, $password));


        // Get user password from DB
        if ($dbPassword != false) {
            // Compare password from db with password entered
            if (crypt($password, $dbPassword) == $dbPassword) {
                //Password are equals

                if (!$pdoUser->validateUserLogin($userNameOrEmail, $password)) {
                    $error->setActiveError(true);

                    $renderController = new RenderController();
                    return $renderController->renderLogin($app, $error);
                }else{
                    $error->setActiveError(false);
                }

                $userName = $pdoUser->getUsername($userNameOrEmail);
                $userId = $pdoUser->getId($userName);


                $sessionController->setSession($app, $userId);

                $app['monolog']->info(sprintf("User with id '%d' and name '%s' logged", $userId, $userName));

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

        $sessionController = new SessionController();
        $pdo = $app['pdo'](PdoMapper::PDO_USER);



        $userUpdate         = $this->getUserFromForm($request);

        $confirmPassword    = $request->request->get('confirm-password');
        $profileImage       = $request->files->get('image-path');


        $userId         = $this->sessionController->getSessionUserId($app);
        $currentUser    = $pdo->get($userId);


        $userUpdate->setEmail($currentUser->getEmail()); // data from db that does not change
        $userUpdate->setId($userId);


        $validator = new Validator();

        $errors = $validator->validateUserUpdate($app, $currentUser, $userUpdate, $confirmPassword);



        if ($profileImage != null) {
            //Image validation
            $formImage = new FormsImageController();
            $formImage->checkUserImage($userUpdate, $validator, $profileImage, $errors);
        }

        if ($validator->haveErrors($errors)) {
            $renderController = new RenderController();
            return $renderController->renderEditProfile($app, $errors);
        }

        //Encrypt user password before insert in database
        $userUpdate->setPassword(crypt($userUpdate->getPassword(), '$2a$07$usesomadasdsadsadsadasdasdasdsadesillystringfors'));
        $userUpdate->setActive(true);
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
        $pdo->update($userUpdate);

        $app['monolog']->info(sprintf("The user with id '%d' has updated its profile", $userId));

        return $app->redirect('/');

    }


}
