<?php

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\Image;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;
use pwgram\Model\Repository\PdoCommentRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use pwgram\Model\Services\PdoMapper;

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

        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);
        $idUser = $pdoUser->getId($this->sessionController->getSessionName($app));

        // Create image entity
        date_default_timezone_set('Europe/Madrid');

        $newImage = new Image($title, date('Y-m-d H:i:s'), $idUser, $private);

        // Save image information in DB image table
        $pdoImage = $app['pdo'](PdoMapper::PDO_IMAGE);

        $pdoImage->add($newImage);

        $idImage = $pdoImage->getLastInsertedId();


        $imageProcessing = new ImageProcessing();
        $imageProcessing->saveUploadImage($idImage, $image->getRealPath());
        return $app -> redirect('/');
    }

    public function deleteImage(Application $app, $idImage){

        $sessionController = new SessionController();
        if($sessionController->correctSession($app)){

            $pdoImage = $app['pdo'](PdoMapper::PDO_IMAGE);
            $pdoComment = $app['pdo'](PdoMapper::PDO_COMMENT);
            $pdoLike = $app['pdo'](PdoMapper::PDO_IMAGE_LIKE);
            $pdoNotification = $app['pdo'](PdoMapper::PDO_NOTIFICATION);



            // Delete image commments
            $comments = $pdoComment->getImageComments($idImage);
            if($comments != null){
                foreach ($comments as $comment) {
                    $pdoComment->remove($comment->getId());
                }
            }

            //delete image notifications
            $notifications = $pdoNotification->getNotificationsImage($idImage);
            if($notifications != null){
                foreach ($notifications as $notification) {
                    $pdoNotification->remove($notification->getId());
                }
            }

            // Delete image likes
            $pdoLike->removeImageLikes($idImage);
            //delete image
            $pdoImage->remove($idImage);

            // Delete image server
            $imageProcessing = new ImageProcessing();
            $imageProcessing->deleteImage($idImage);

            return $app -> redirect('/user-images');
        } else return $app -> redirect('/login');

    }


    public function editImageForm(Application $app, Request $request, $idImage){

        $validator          = new Validator();
        $sessionController  = new SessionController();

        if($sessionController->correctSession($app)){

            $pdo = $app['pdo'](PdoMapper::PDO_IMAGE);

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

            $pdo->update($newImage);

            return $app -> redirect('/user-images');


        } else  return $app -> redirect('/login');

    }
}