<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:17
 */

namespace pwgram\Controller;


use pwgram\Model\AppFormatDate;
use pwgram\Model\Repository\PdoCommentRepository;
use Silex\Application;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;


class CommentsController
{

    private $sessionController;


    public function __construct()
    {
        $this->sessionController = new SessionController();
    }


    public function addComment(Application $app, Request $request) {

        $imageId = $request->get("id");
        $content = $request->get('text');

        $userid = $this->sessionController->getSessionUserId($app);
        if (!$userid) {

            // TODO 403?

            return $app['twig']->render('error.twig', array(
                'message'=>"El comentario no se ha añadido, usario no conectado."
            ));
        }


        if (strlen(preg_replace('/\s+/u','',$content))){ //  que no pongan solo espacion en blanco
            $db = Database::getInstance("pwgram");
            $pdo = new PdoCommentRepository($db);

            $today = AppFormatDate::today();
            $comment = new Comment($content, $userid, $today, $imageId);

            $res = $pdo->add($comment);

            if (!$res) {

                return $app['twig']->render('error.twig', array(
                    'message'=>"No se ha podido añadir el comentario en la imagen solicitada."
                ));
            }
        }


        return $app->redirect("/"); // TODO: add an information message or something similar
    }



}