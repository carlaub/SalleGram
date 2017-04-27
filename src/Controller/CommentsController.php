<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:17
 */

namespace pwgram\Controller;


use pwgram\Model\AppFormatDate;
use pwgram\Model\Entity\Notification;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
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

            // TODO 403

            return $app['twig']->render('error.twig', array(
                'message'=>"El comentario no se ha añadido, usario no conectado."
            ));
        }

        $db = Database::getInstance("pwgram");
        $pdoComment = new PdoCommentRepository($db);
        $pdoNotification = new PdoNotificationRepository($db);
        $pdoImage = new PdoImageRepository($db);

        //  que no pongan solo espacion en blanco y no hayan publicado un comentario antes
        if (strlen(preg_replace('/\s+/u','',$content)) && $pdoComment->commentValid($app,$imageId,$userid )){

            $today = AppFormatDate::today();
            $comment = new Comment($content, $userid, $today, $imageId);

            $res = $pdoComment->add($app, $comment);

            //Add notification
            $idAuthor = $pdoImage->getAuthor($app, $imageId);

            //Create new notification
            $notification = new Notification($idAuthor, $userid, Notification::TYPE_COMMENT, $imageId, date('Y-m-d H:i:s'));
            //Update  notifications
            $pdoNotification->add($app, $notification);

            if (!$res) {

                return $app['twig']->render('error.twig', array(
                    'message'=>"No se ha podido añadir el comentario en la imagen solicitada."
                ));
            }
        }

        return $app->redirect("/"); // TODO: add an information message or something similar
    }

    public function editComment(Application $app, Request $request, $idComment){

        $userid = $this->sessionController->getSessionUserId($app);
        if (!$userid) {

            // TODO 403

            return $app['twig']->render('error.twig', array(
                'message'=>"El comentario no se ha añadido, usario no conectado."
            ));
        }


        $db = Database::getInstance("pwgram");
        $pdo = new PdoCommentRepository($db);

        $content = $request->get('text');


        if (strlen(preg_replace('/\s+/u','',$content))) {
            //edit comment
            $today = AppFormatDate::today();
            $comment = new Comment($content, 0, $today, 0, 0);
            $comment->setId($idComment);

            $pdo->update($app, $comment);

        }else{
            //request empty, delete comment

            $pdo->remove($app, $idComment);
        }
        return $app -> redirect('/user-comments');

    }

    public function deleteComment(Application $app, Request $request, $idComment){

        $userid = $this->sessionController->getSessionUserId($app);
        if (!$userid) {

            // TODO 403

            return $app['twig']->render('error.twig', array(
                'message'=>"El comentario no se ha añadido, usario no conectado."
            ));
        }


        $db = Database::getInstance("pwgram");
        $pdo = new PdoCommentRepository($db);

        $pdo->remove($app, $idComment);


        return $app -> redirect('/user-comments');

    }



}