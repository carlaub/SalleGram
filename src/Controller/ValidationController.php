<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;

class validationController {
    public function userValidation(Application $app, $id) {
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);
        $pdoUser->updateActiveState($id);

        //TODO: loggear usuario
        return $app -> redirect('/');
    }
}