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

        $userid = $this->sessionController->verifySession($app);
        if (!$userid) {

            // TODO 403?

            return $app['twig']->render('error.twig', array(
                'message'=>"El like no se ha añadido, usario no conectado."
            ));
        }

        $db = Database::getInstance("pwgram");
        $pdoImageLike = new PdoImageLikesRepository($db);
        $pdoUser = new PdoUserRepository($db);

        $idUser = $pdoUser->getId($app['session']->get('user')['username']);

        //Validate that user not put like on this photo
        if ($pdoImageLike->likevalid($id, $idUser)) {
            $newLike = new ImageLike($idUser, $id);

            $pdoImageLike->add($newLike);
            $this->updateImageLikes($id);

            return $app->redirect('/');
        }

        return $app['twig']->render('error.twig', array(
            'message'=>"El like no se ha añadido, usario no conectado."
        ));

    }

    /**
     * @param $idImage
     */
    private function updateImageLikes($idImage) {
        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);

        $pdoImage->updateLikes($idImage);
    }
}