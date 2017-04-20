<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoImageRepository;

class UserUploadImagesController {

    public function getUserUploadImages($idUser) {
        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);

        return $pdoImage->getAllUserImages($idUser);
    }
}