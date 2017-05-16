<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:17
 */

namespace pwgram\Controller;


use pwgram\Model\AppFormatDate;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\Notification;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


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

            $response = new Response();
            $content = $app['twig']->render('error.twig', array(
                'message'=>"El comentario no se ha añadido, usario no conectado."
            ));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN); // 403 code
            return $response;
        }

        $db = Database::getInstance("pwgram");
        $pdoComment = new PdoCommentRepository($db);
        $pdoNotification = new PdoNotificationRepository($db);
        $pdoImage = new PdoImageRepository($db);

        //  que no pongan solo espacion en blanco y no hayan publicado un comentario antes
        if (strlen(preg_replace('/\s+/u','',$content)) && $pdoComment->commentValid($app,$imageId,$userid )){

            $today = AppFormatDate::today();
            $comment = new Comment($content, $userid, $today, $imageId);

            // Scape html tags
            $comment->setContent(strip_tags($comment->getContent()));

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
            return $app -> redirect('/');
        } else {
            $renderController = new RenderController();
            $error = new FormError();
            $error->setCommentError(true);
            return $renderController->renderHome($app, $error);
        }


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

    public function onShowMoreComments(Application $app, $idImage, $lastComment) {

        $db = Database::getInstance("pwgram");
        $pdo = new PdoCommentRepository($db);
        $userPdo = new PdoUserRepository($db);

        $nextComments = $pdo->getImageComments($app, $idImage, $lastComment, PdoCommentRepository::APP_MAX_COMMENTS_PAGINATED);

        foreach ($nextComments as $comment) {

            $user = $userPdo->get($app, $comment->getFkUser());
            $comment->setUserName($user->getUsername());
        }

        $result = array(

            'loaded'    =>  count($nextComments),
            'image'     =>  $idImage,
            'comments'  =>  $app['objects_json_parser']->objectToJson($nextComments),
            'total_image_comments' => $pdo->getTotalImageComments($app, $idImage)
        );


        return json_encode($result);
    }
}