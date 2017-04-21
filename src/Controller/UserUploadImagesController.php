<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoImageRepository;
use Silex\Application;

class UserUploadImagesController {

    public function getUserUploadImages(Application $app, $idUser) {
        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);

        return $pdoImage->getAllUserImages($app, $idUser);
    }
}