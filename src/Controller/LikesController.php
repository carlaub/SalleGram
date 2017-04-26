<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\ImageLike;
use pwgram\Model\Entity\Like;
use pwgram\Model\Entity\Notification;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;

class LikesController {
    private $sessionController;


    public function __construct()
    {
        $this->sessionController = new SessionController();
    }

    public function addLike(Application $app, int $id) {

        $userid = $this->sessionController->getSessionUserId($app);
        if (!$userid) {

            // TODO 403?

            return $app['twig']->render('error.twig', array(
                'message'=>"El like no se ha añadido, usario no conectado."
            ));
        }

        $db = Database::getInstance("pwgram");
        $pdoImageLike = new PdoImageLikesRepository($db);
        $pdoUser = new PdoUserRepository($db);
        $pdoNotification = new PdoNotificationRepository($db);
        $pdoImage = new PdoImageRepository($db);

        $idUser = $pdoUser->getId($app,$this->sessionController->getSessionName($app));

        //Validate that user not put like on this photo
        if ($pdoImageLike->likevalid($app, $id, $idUser)) {
            $newLike = new ImageLike($idUser, $id);

            $pdoImageLike->add($app, $newLike);
            $this->updateImageLikes($app, $id);

            $idAuthor = $pdoImage->getAuthor($app, $id);
            //Create new notification
            $notification = new Notification($idAuthor, $idUser, Notification::TYPE_LIKE, $id, date('Y-m-d H:i:s'));
            //Update  notifications
            $pdoNotification->add($app, $notification);

            return $app->redirect('/');
        }else{


            $pdoImageLike->removeLike($app, $id, $this->sessionController->getSessionUserId($app));
            $pdoImage->updateLikes($app, $id, -1);

            return $app->redirect('/');
        }

//        return $app['twig']->render('error.twig', array(
//            'message'=>"Ya le has dado like a esta foto"
//        ));

    }

    /**
     * @param $idImage
     */
    private function updateImageLikes(Application $app, $idImage) {
        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);

        $pdoImage->updateLikes($app, $idImage);
    }
}