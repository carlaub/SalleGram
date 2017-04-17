<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;


class RenderController {

    public function renderHome(Application $app) {
        //TODO: Comprobar que el usuario de la sesion es correcto
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        //var_dump($app['session']->get('user')['username']);
        $idUser = $this->verifySession($app);
        $image = $this->getProfileImage($idUser);

        return $app['twig']->render('home.twig', array(
            'app'=> ['name' => $app['app.name']],
            'name'=> $app['session']->get('user')['username'],
            'img'=> $image,
            'logged'=> $idUser,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderLogin(Application $app) {
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('login.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>$this->haveSession($app),
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

    public function haveSession(Application $app) {
        //var_dump($app['session']->get('user'));

        if ($app['session']->get('user') === null){
            return false;
        }
        return true;

    }

    public function logout(Application $app) {
        $app['session']->clear();//solo una sesion a la vez
        return $this->renderHome($app);
    }

    public function renderUploadImage(Application $app) {
       /* if ($app['session']->get('user') === null){
            //TODO 403 code
           return $this->renderHome($app);
        }
        return $app['twig']->render('uploadImage.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>$this->haveSession($app),
        ));*/
       if ($this->correctSession($app)){
           return $app['twig']->render('uploadImage.twig', array(
               'app'=> ['name' => $app['app.name']],
               'logged'=>$this->haveSession($app),
           ));
       }
       return $app -> redirect('/');
    }

    /**
     * @param $app
     */
    public function verifySession($app) {

        if ($this->haveSession($app)) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);
            $id = $pdoUser->validateUserSession($app['session']->get('user')['username'],
                $app['session']->get('user')['password']);

            if ($id != false) return $id;
        }
        return false;
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

    /**
     *
     * TODO LLAMAR A ESTA FUCNION ANTES DE REENDERIZAR CUALQUIERA QUE NECESITE ESTAR LOGEADO ...
     * TODO ... SI DEVUELVE FALSE REENDERIZAR /LOGIN
     *
     * @param $app
     * @return bool
     */
    public function correctSession($app) {

        if ($app['session']->get('user') != null) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);
            if($pdoUser->validateUserLogin($app['session']->get('user')['username'],
                $app['session']->get('user')['password'])){
                return true;
            }
        }
        //TODO error 403
        return false;
    }
}

