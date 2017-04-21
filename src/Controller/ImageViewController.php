<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;

class ImageViewController {

    /**
     * Load required information in Image like comments and username
     * @param $idImage
     */
    public function prepareImage($idImage) {
        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);
        $pdoUser = new PdoUserRepository($db);
        $pdoComents = new PdoCommentRepository($db);

        // Verify that the image exist
        $image = $pdoImage->get($app, $idImage);

        // Image not found
        if(!$image) return false;

        // Image found
        //Set Username
        $image->setUserName($pdoUser->getName($app, $image->getFkUser()));
        //Set Comment
        $comments = $pdoComents->getImageComments($app, $idImage);
        if ($comments) $image->setComments($comments);

        //Increment image visits
        $this->incrementVisits($idImage, $pdoImage);

        return $image;
    }

    private function incrementVisits($idImage, PdoImageRepository $pdoImage) {
        $pdoImage->incrementVisits($idImage);
    }
}