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
use Silex\Application;
use pwgram\Model\AppFormatDate;


class HomeController
{
    private $sessionController;

    function onShowMoreImages(Application $app, $lastImage) {


        $this->sessionController = new SessionController();

        $renderController = new RenderController();

        $db = Database::getInstance("pwgram");

        $pdo        = new PdoImageRepository($db);
        $commentsPdo = new PdoCommentRepository($db);
        $userPdo      = new PdoUserRepository($db);
        $likesPdo       = new PdoImageLikesRepository($db);

        $nextImages = $pdo->getAllPublicImages($app, $lastImage, PdoImageRepository::APP_MAX_IMG_PAGINATED);

        $imagesDatesFormatted   = [];
        $commentsPerImage       = [];

        $total = 0;
        // let's add all the comments for each image
        foreach ($nextImages as $image) {

            $total++;

            $comments = $commentsPdo->getImageComments($app, $image->getId(), 0, 3);
            //Set the name of username of the comment
            if (!$comments) $comments = [];
            else{
                foreach ($comments as $commentUser) {

                    $commentUser->setUserName($userPdo->getName($app, $commentUser->getFkUser()));
                }
            }

            $image->setComments($comments);
            array_push($commentsPerImage, $app['objects_json_parser']->objectToJson($comments));

            $userName = $userPdo->getName($app, $image->getFkUser());
            $image->setUserName($userName);
            $image->setLiked(!($likesPdo->likevalid($app, $image->getId(), $this->sessionController->getSessionUserId($app))));

            $image->setNumComments($commentsPdo->getTotalImageComments($app, $image->getId()));

            array_push($imagesDatesFormatted, AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt())));
        }

        return json_encode(array(

            'logged' => $this->sessionController->correctSession($app),
            'images' => $app['objects_json_parser']->objectToJson($nextImages),
            'comments' => json_encode($commentsPerImage),
            'dates'  => json_encode($imagesDatesFormatted),
            'loaded' => json_encode($total),
            'total_public_images' => json_encode($pdo->getTotalOfPublicImages($app))
        ));
        //return json_encode($imagesDatesFormatted);
    }

}