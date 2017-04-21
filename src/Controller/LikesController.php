<?php

namespace pwgram\Controller;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\ImageLike;
use pwgram\Model\Entity\Like;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
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

        $idUser = $pdoUser->getId($app, $app['session']->get('user')['username']);

        //Validate that user not put like on this photo
        if ($pdoImageLike->likevalid($app, $id, $idUser)) {
            $newLike = new ImageLike($idUser, $id);

            $pdoImageLike->add($app, $newLike);
            $this->updateImageLikes($app, $id);

            return $app->redirect('/');
        }

        return $app['twig']->render('error.twig', array(
            'message'=>"Ya le has dado like a esta foto"
        ));

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