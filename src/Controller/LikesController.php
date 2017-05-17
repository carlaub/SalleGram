<?php

namespace pwgram\Controller;

use pwgram\Model\Services\PdoMapper;
use pwgram\Model\Entity\ImageLike;
use pwgram\Model\Entity\Like;
use pwgram\Model\Entity\Notification;

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

        $pdoImageLike       = $app['pdo'](PdoMapper::PDO_IMAGE_LIKE);
        $pdoUser            = $app['pdo'](PdoMapper::PDO_USER);
        $pdoNotification    = $app['pdo'](PdoMapper::PDO_NOTIFICATION);
        $pdoImage           = $app['pdo'](PdoMapper::PDO_IMAGE);

        $idUser = $pdoUser->getId($this->sessionController->getSessionName($app));

        //Validate that user not put like on this photo
        if ($pdoImageLike->likevalid($id, $idUser)) {
            $newLike = new ImageLike($idUser, $id);

            $pdoImageLike->add($newLike);
            $this->updateImageLikes($app, $id);

            $idAuthor = $pdoImage->getAuthor($id);
            //Create new notification
            $notification = new Notification($idAuthor, $idUser, Notification::TYPE_LIKE, $id, date('Y-m-d H:i:s'));
            //Update  notifications
            $pdoNotification->add($notification);

            return $app->redirect('/');
        }else{


            $pdoImageLike->removeLike($id, $this->sessionController->getSessionUserId($app));
            $pdoImage->updateLikes($id, -1);

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

        $pdoImage = $app['pdo'](PdoMapper::PDO_IMAGE);

        $pdoImage->updateLikes($idImage);
    }
}