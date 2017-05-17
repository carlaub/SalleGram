<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use pwgram\Model\Services\PdoMapper;

class ImageViewController {

    /**
     * Load required information in Image like comments and username
     *
     * @param Application $app
     * @param $idImage
     *
     * @return Image
     */
    public function prepareImage(Application $app, $idImage) {

        $pdoImage   = $app['pdo'](PdoMapper::PDO_IMAGE);
        $pdoUser    = $app['pdo'](PdoMapper::PDO_USER);
        $pdoComents = $app['pdo'](PdoMapper::PDO_COMMENT);

        $image = $pdoImage->get($idImage);

        // Image not found
        if(!$image) return false;

        //is a private image?
        if($image->isPrivate()){
            $session = new SessionController();
            if($session->getSessionUserId($app) != $image->getFkUser()) return false;

        }

        //Increment image visits
        $this->incrementVisits($app, $idImage, $pdoImage);


        // Verify that the image exist
        // Image found
        $image = $pdoImage->get($idImage);//return doing this for the increment of visits
        //Set Username
        $image->setUserName($pdoUser->getName($image->getFkUser()));
        //Set Comment
        $comments = $pdoComents->getImageComments($idImage, 0, PdoCommentRepository::APP_MAX_COMMENTS_PAGINATED);
        if ($comments){
            //Set the name of username of the comment
            foreach ($comments as $commentUser) {

                $commentUser->setUserName($pdoUser->getName($commentUser->getFkUser()));

                $commentUser->setFkUser(($this->getProfileImage($app, $commentUser->getFkUser())));//reutilitzo fk user per posar la foto
            }
            $image->setComments($comments);
        }

//        //Increment image visits
//        $this->incrementVisits($app, $idImage, $pdoImage);

        return $image;
    }

    private function incrementVisits(Application $app, $idImage, PdoImageRepository $pdoImage) {


        $pdoImage->incrementVisits($idImage);
    }

    public function getProfileImage(Application $app, $idUser)
    {
        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);

        if ($pdoUser->getProfileImage($idUser)) {
            return $idUser;
        }
        return "img_profile_default";
    }

}