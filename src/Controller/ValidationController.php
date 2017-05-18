<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoUserRepository;
use pwgram\Model\Services\PdoMapper;
use Silex\Application;

class validationController {
    public function userValidation(Application $app, $id) {


        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);


        //Check if user account in already active
        if ($pdoUser->getActive($id)) {
            return $app['twig']->render('error.twig',array(
                'message'=>"La cuenta de usuario ya està activada",
            ));
        }
        //Update user active state in db
        $pdoUser->updateActiveState($id);


        $sessionController = new SessionController();

        $sessionController->setSession($app, $id);
        return $app['twig']->render('welcome.twig',array());
    }
}