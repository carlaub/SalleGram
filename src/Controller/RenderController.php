<?php

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;


class RenderController {

    private $sessionController;


    public function __construct()
    {
        $this->sessionController = new SessionController();
    }

    public function renderHome(Application $app) {
        //TODO: Comprobar que el usuario de la sesion es correcto
        //TODO: This is a first solution but must be rethinked for pagination

        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);
        $commentsPdo = new PdoCommentRepository($db);

        //Images array that will be displayed on the main page
        $publicImages = $pdoImage->getAllPublicImages();
        $publicImages = !$publicImages? [] : $publicImages; // if false, return an empty array, if not return the public images

        // let's add all the comments for each image
        foreach ($publicImages as $image) {

            $comments = $commentsPdo->getImageComments($image->getId());

            if (!$comments) $comments = [];
            $image->setComments($comments);
        }

        //var_dump($app['session']->get('user')['username']);
        $idUser = $this->sessionController->verifySession($app);
        $image = $this->getProfileImage($idUser);

        if ($publicImages != 0) {
            return $app['twig']->render('home.twig', array(
                'app'=> ['name' => $app['app.name']],
                'name'=> $app['session']->get('user')['username'],
                'img'=> $image,
                'logged'=> $idUser,
                'images'=>$publicImages //SERA UN ARRAY
            ));
        }else{
            return $app['twig']->render('homeWelcome.twig', array(
                'app'=> ['name' => $app['app.name']],
                'name'=> $app['session']->get('user')['username'],
                'img'=> $image,
                'logged'=> $idUser,
                'images'=>$publicImages //SERA UN ARRAY
            ));
        }

    }

    public function renderLogin(Application $app) {
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('login.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>$this->sessionController->haveSession($app),
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderRegistration(Application $app) {
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('register.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));
    }

    public function renderEditProfile(Application $app) {
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges

        return $app['twig']->render('edit_profile.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }


    public function renderValidation(Application $app) {
        return $app['twig']->render('validation.twig', array(

        ));
    }


    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function renderUploadImage(Application $app) {
       /* if ($app['session']->get('user') === null){
            //TODO 403 code
           return $this->renderHome($app);
        }
        return $app['twig']->render('uploadImage.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>$this->haveSession($app),
        ));*/
       if ($this->sessionController->correctSession($app)){
           return $app['twig']->render('uploadImage.twig', array(
               'app'=> ['name' => $app['app.name']],
               'logged'=>$this->haveSession($app),
           ));
       }
       return $app -> redirect('/login');
    }


    public function logout(Application $app) {
        $app['session']->clear();//solo una sesion a la vez
        return $this->renderHome($app);
    }



    /**
     * @param $idUser
     * @return string
     */
    public function getProfileImage($idUser){
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);

        if($pdoUser->getProfileImage($idUser)) {
            return $idUser;
        }
        return "img_profile_default";
    }


}

