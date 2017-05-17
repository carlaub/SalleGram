<?php

namespace pwgram\Controller;

use pwgram\Model\Services\PdoMapper;
use Silex\Application;

class UserUploadImagesController {

    public function getUserUploadImages(Application $app, $idUser) {

        $pdoImage = $app['pdo'](PdoMapper::PDO_IMAGE);

        return $pdoImage->getAllUserImages($idUser);
    }
}