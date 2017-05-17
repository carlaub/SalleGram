<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 27/04/17
 * Time: 19:57
 */

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use pwgram\Model\Services\PdoMapper;
use Silex\Application;
use pwgram\Model\AppFormatDate;


class HomeController
{
    private $sessionController;

    function onShowMoreImages(Application $app, $lastImage) {

        $this->sessionController = new SessionController();

        $pdo            = $app['pdo'](PdoMapper::PDO_IMAGE);
        $commentsPdo    = $app['pdo'](PdoMapper::PDO_COMMENT);
        $userPdo        = $app['pdo'](PdoMapper::PDO_USER);
        $likesPdo       = $app['pdo'](PdoMapper::PDO_IMAGE_LIKE);

        $nextImages = $pdo->getAllPublicImages($lastImage, PdoImageRepository::APP_MAX_IMG_PAGINATED);

        $imagesDatesFormatted   = [];
        $commentsPerImage       = [];

        $total = 0;
        // let's add all the comments for each image
        foreach ($nextImages as $image) {

            $total++;

            $comments = $commentsPdo->getImageComments($image->getId(), 0, 3);
            //Set the name of username of the comment
            if (!$comments) $comments = [];
            else{
                foreach ($comments as $commentUser) {

                    $commentUser->setUserName($userPdo->getName($commentUser->getFkUser()));
                    $commentUser->setFkUser(($this->getProfileImage($app, $commentUser->getFkUser())));//reutilitzo fk user per posar la foto
                }
            }

            $image->setComments($comments);
            array_push($commentsPerImage, $app['objects_json_parser']->objectToJson($comments));

            $userName = $userPdo->getName($image->getFkUser());
            $image->setUserName($userName);
            $image->setLiked(!($likesPdo->likevalid($image->getId(), $this->sessionController->getSessionUserId($app))));

            $image->setNumComments($commentsPdo->getTotalImageComments($image->getId()));

            array_push($imagesDatesFormatted, AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt())));
        }

        return json_encode(array(

            'logged'    => $this->sessionController->correctSession($app),
            'images'    => $app['objects_json_parser']->objectToJson($nextImages),
            'comments'  => json_encode($commentsPerImage),
            'dates'     => json_encode($imagesDatesFormatted),
            'loaded'    => json_encode($total),
            'total_public_images' => json_encode($pdo->getTotalOfPublicImages())
        ));
        //return json_encode($imagesDatesFormatted);
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