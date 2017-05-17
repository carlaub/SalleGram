<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:17
 */

namespace pwgram\Controller;


use pwgram\Model\Services\PdoMapper;
use pwgram\Model\AppFormatDate;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\Notification;
use pwgram\Model\Repository\PdoCommentRepository;
use Silex\Application;
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

        $pdoComment         = $app['pdo'](PdoMapper::PDO_COMMENT);
        $pdoNotification    = $app['pdo'](PdoMapper::PDO_NOTIFICATION);
        $pdoImage           = $app['pdo'](PdoMapper::PDO_IMAGE);

        //  que no pongan solo espacion en blanco y no hayan publicado un comentario antes
        if (strlen(preg_replace('/\s+/u','',$content)) && $pdoComment->commentValid($imageId, $userid )){

            $today = AppFormatDate::today();
            $comment = new Comment($content, $userid, $today, $imageId);

            // Scape html tags
            $comment->setContent(strip_tags($comment->getContent()));

            if(!strlen(preg_replace('/\s+/u','',$content))){
                $renderController = new RenderController();
                $error = new FormError();
                $error->setCommentError(true);
                return $renderController->renderHome($app, $error);
            }

            $res = $pdoComment->add($comment);

            //Add notification
            $idAuthor = $pdoImage->getAuthor($imageId);

            //Create new notification
            $notification = new Notification($idAuthor, $userid, Notification::TYPE_COMMENT, $imageId, date('Y-m-d H:i:s'));
            //Update  notifications
            $pdoNotification->add($notification);

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

        $pdo = $app['pdo'](PdoMapper::PDO_COMMENT);

        $content = $request->get('text');


        if (strlen(preg_replace('/\s+/u','',$content))) {
            //edit comment


            $today = AppFormatDate::today();
            $comment = new Comment($content, 0, $today, 0, 0);

            // Scape html tags
            $comment->setContent(strip_tags($comment->getContent()));

            if(!strlen(preg_replace('/\s+/u','',$content))){
                $renderController = new RenderController();
                $error = new FormError();
                $error->setCommentError(true);
                return $renderController->renderHome($app, $error);
            }

            $comment->setId($idComment);

            $pdo->update($comment);

        }else{
            //request empty, delete comment

            $pdo->remove($idComment);
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

        $pdo = $app['pdo'](PdoMapper::PDO_COMMENT);

        $pdo->remove($idComment);


        return $app -> redirect('/user-comments');

    }


    /**
     * Returns a new set of comments in JSON format. This method is used for comments pagination.
     *
     * @param Application $app
     * @param int $idImage          The id of the image which the comments want to be recovered.
     * @param int $lastComment          The last comment that exists so that it has been loaded already.
     * @return string
     */
    public function onShowMoreComments(Application $app, $idImage, $lastComment) {

        $pdo = $app['pdo'](PdoMapper::PDO_COMMENT);
        $userPdo = $app['pdo'](PdoMapper::PDO_USER);

        $nextComments = $pdo->getImageComments($idImage, $lastComment, PdoCommentRepository::APP_MAX_COMMENTS_PAGINATED);

        foreach ($nextComments as $comment) {

            $user = $userPdo->get($comment->getFkUser());
            $comment->setUserName($user->getUsername());
            $comment->setFkUser(($this->getProfileImage($app, $comment->getFkUser())));//reutilitzo fk user per posar la foto
        }

        $result = array(

            'loaded'    =>  count($nextComments),
            'image'     =>  $idImage,
            'comments'  =>  $app['objects_json_parser']->objectToJson($nextComments),
            'total_image_comments' => $pdo->getTotalImageComments($idImage)
        );


        return json_encode($result);
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