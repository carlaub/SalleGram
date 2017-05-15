<?php

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\Image;
use pwgram\Model\Entity\Comment;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;
use pwgram\Model\Repository\PdoCommentRepository;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Exception\ValidatorException;

class FormsImageController {

    private $request;

    private $sessionController;


    public function __construct()
    {
        $this->sessionController = new SessionController();
    }



    public function checkUserImage(User &$user, Validator $validator, $profileImage, $error) {
        if ($error === null) $error = new FormError();
        //Image validation
        if ($validator->validateImage($profileImage->getClientSize(), $profileImage->getClientOriginalExtension(), $error)) {

            $user->setProfileImage(true);
            return true;
        }

        return false;
    }

    /**
     * Image upload. Verify data form, image characteristics (size, extension...), update BBDD and
     * save the image in two different size: 400x300 and 100x100
     * The users image are saved in upload_img directory inside web/assets/img
     * The users image follow this nomenclature:
     *  - {id-image}100x100.{extension}
     *  - {id-image}400x300.{extension}
     *
     * @param Application $app
     * @param Request $request
     * @param Database $db
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadImage(Application $app, Request $request) {

        $db = Database::getInstance("pwgram");

        $validator = new Validator();

        $title = $request->request->get('img-title');
        $private = $request->request->get('img-private') != null;
        $image = $request->files->get('img-selected');

        $errors = new FormError();
        // Check if the image accomplish the requirements
        if (!$validator->validateUploadImage($title, $image, $errors)) {

            $renderController = new RenderController();
            return $renderController->renderUploadImage($app, $errors);
        }

        // Correct image, save it and update DB

        $pdoUser = new PdoUserRepository($db);
        $idUser = $pdoUser->getId($app, $this->sessionController->getSessionName($app));

        // Create image entity
        date_default_timezone_set('Europe/Madrid');

        $newImage = new Image($title, date('Y-m-d H:i:s'), $idUser, $private);

        // Save image information in DB image table
        $pdoImage = new PdoImageRepository($db);

        $pdoImage->add($app, $newImage);

        $idImage = $pdoImage->getLastInsertedId($app);


        $imageProcessing = new ImageProcessing();
        $imageProcessing->saveUploadImage($idImage, $image->getRealPath());
        return $app -> redirect('/');
    }

    public function deleteImage(Application $app, $idImage){

        $sessionController = new SessionController();
        if($sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");

            $pdoImage = new PdoImageRepository($db);
            $pdoComment = new PdoCommentRepository($db);
            $pdoLike = new PdoImageLikesRepository($db);
            $pdoNotification = new PdoNotificationRepository($db);



            // Delete image commments
            $comments = $pdoComment->getImageComments($app, $idImage);
            if($comments != null){
                foreach ($comments as $comment) {
                    $pdoComment->remove($app, $comment->getId());
                }
            }

            //delete image notifications
            $notifications = $pdoNotification->getNotificationsImage($app, $idImage);
            if($notifications != null){
                foreach ($notifications as $notification) {
                    $pdoNotification->remove($app, $notification->getId());
                }
            }

            // Delete image likes
            $pdoLike->removeImageLikes($app, $idImage);
            //delete image
            $pdoImage->remove($app, $idImage);

            // Delete image server
            $imageProcessing = new ImageProcessing();
            $imageProcessing->deleteImage($idImage);

            return $app -> redirect('/user-images');
        }else return $app -> redirect('/login');

    }


    public function editImageForm(Application $app, Request $request, $idImage){
        $db = Database::getInstance("pwgram");

        $validator = new Validator();


        $sessionController = new SessionController();
        if($sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");

            $pdo = new PdoImageRepository($db);

            $title = $request->request->get('img-title');
            $private = $request->request->get('img-private') != null;

            $newImage = new Image($title, date('Y-m-d H:i:s'), 0, $private);
            $newImage->setId($idImage);

            $errors = new FormError();
            // Check if the image accomplish the requirements
            if (!$validator->validateEditImage($title, $errors)) {

                $renderController = new RenderController();
                return $renderController->renderUploadImage($app, $errors);
            }

            $pdo->update($app, $newImage);

            return $app -> redirect('/user-images');


        } else  return $app -> redirect('/login');

    }
}