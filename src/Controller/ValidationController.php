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
        if ($pdoUser->getActive($id)) {
            return $app['twig']->render('error.twig',array(
                'message'=>"La cuenta de usuario ya està activada",
            ));
        }
        //Update user active state in db
        if ($pdoUser->updateActiveState($id)){
            //TODO: loggear usuario
            return $app -> redirect('/');
        }
        return $app['twig']->render('error.twig',array(
            'message'=>"El usuario no existe",
        ));
    }
}