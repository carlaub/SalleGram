<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;

class validationController {
    public function userValidation(Application $app, $id) {
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);


        //Check if user account in already active
        if ($pdoUser->getActive($app, $id)) {
            return $app['twig']->render('error.twig',array(
                'message'=>"La cuenta de usuario ya està activada",
            ));
        }
        //Update user active state in db
        $pdoUser->updateActiveState($app, $id);


        $sessionController = new SessionController();

        $sessionController->setSession($app, $id);
        //TODO: loggear usuario
        return $app['twig']->render('welcome.twig',array(
        ));
    }
}