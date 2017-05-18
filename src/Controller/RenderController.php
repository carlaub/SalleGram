<?php

namespace pwgram\Controller;

use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoFollowRepository;
use pwgram\Model\Services\PdoMapper;
use Silex\Application;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\AppFormatDate;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;



class RenderController
{

    private $sessionController;


    public function __construct() {

        $this->sessionController = new SessionController();
    }

    /**
     * @param Application $app
     * @param null $publicImages
     * @param bool $mostVisitedLayout
     * @return mixed
     */
    public function renderHome(Application $app, $error = null,  $publicImages = null, $mostVisitedLayout = false)
    {
        $commentsPdo    = $app['pdo'](PdoMapper::PDO_COMMENT);
        $userPdo        = $app['pdo'](PdoMapper::PDO_USER);
        $imagesPdo      = $app['pdo'](PdoMapper::PDO_IMAGE);
        $likesPdo       = $app['pdo'](PdoMapper::PDO_IMAGE_LIKE);


        if ($publicImages == null) {

            //Images array that will be displayed on the main page
            $publicImages = $imagesPdo->getAllPublicImages(0, PdoImageRepository::APP_MAX_IMG_PAGINATED);
            $publicImages = !$publicImages ? [] : $publicImages; // if false, return an empty array, if not return the public images
        }
        $imagesDatesFormatted = [];

        // let's add all the comments for each image
        foreach ($publicImages as $image) {

            $comments = $commentsPdo->getImageComments($image->getId(), 0, 3);
            //Set the name of username of the comment
            if (!$comments) $comments = [];
            else {
                foreach ($comments as $commentUser) {

                    $commentUser->setUserName($userPdo->getName($commentUser->getFkUser()));
                    $commentUser->setFkUser(($this->getProfileImage($app, $commentUser->getFkUser())));//reutilitzo fk user per posar la foto
                }
            }

            $image->setComments($comments);

            $userName = $userPdo->getName($image->getFkUser());
            $image->setUserName($userName);
            $image->setLiked(!($likesPdo->likevalid($image->getId(), $this->sessionController->getSessionUserId($app))));

            $image->setNumComments($commentsPdo->getTotalImageComments($image->getId()));

            array_push($imagesDatesFormatted, AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt())));
        }

        $error = $this->sessionController->getExistingError($app);
        if ($error != null) $this->sessionController->clearError($app);

        $image = $this->getProfileImage($app, $this->sessionController->getSessionUserId($app));
        if ($error == null) {


            $error = new FormError();
        }

        if ($publicImages != null) {

            return $app['twig']->render('home.twig', array(
                'app'       => ['name' => $app['app.name']],
                'name'      => $this->sessionController->getSessionName($app),
                'img'       => $image,
                'logged'    => $this->sessionController->haveSession($app),
                'images'    => $publicImages,
                'dates'     => $imagesDatesFormatted,
                'is_most_visited_layout' => $mostVisitedLayout,
                'errors'    => $error
            ));

        } else {
            return $app['twig']->render('homeWelcome.twig', array(
                'app'   => ['name' => $app['app.name']],
                'name'  => $this->sessionController->getSessionName($app),
                'img'   => $image,
                'logged' => $this->sessionController->haveSession($app),
                'p'     => 'Sube fotos y compártelas con tus amigos ',
                'images' => $publicImages,
                'dates' => $imagesDatesFormatted,
                'is_most_visited_layout' => $mostVisitedLayout
            ));
        }

    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function renderMostVisited(Application $app){

        $imagesPdo = $app['pdo'](PdoMapper::PDO_IMAGE);

        $imagesPdo = $imagesPdo->getMostVisitedImages();

        return $this->renderHome($app, null, $imagesPdo, true);
    }

    /**
     * @param Application $app
     * @param null $errors
     * @return mixed
     */
    public function renderLogin(Application $app, $errors = null)
    {
        if ($errors == null) $errors = new FormError();

        $TotaInfoDeFotos = 0;
        return $app['twig']->render('login.twig', array(
            'app'       => ['name' => $app['app.name']],
            'logged'    => $this->sessionController->haveSession($app),
            'data'      => $TotaInfoDeFotos,
            'errors'    => $errors
        ));

    }

    /**
     * @param Application $app
     * @param null $errors
     * @param null $user
     * @return mixed
     */
    public function renderRegistration(Application $app, $errors = null, $user = null)
    {
        $TotaInfoDeFotos = 0;

        if ($errors == null) $errors = new FormError();
        if ($user == null) $user = new User("", "", "", 0);

        return $app['twig']->render('register.twig', array(
            'app'           => ['name' => $app['app.name']],
            'logged'        => false,
            'data'          => $TotaInfoDeFotos,
            'errors'        => $errors,
            'user'          => $user
        ));
    }

    /**
     * @param Application $app
     * @param null $errors
     * @return mixed
     */
    public function renderEditProfile(Application $app, $errors = null)
    {

        if ($errors == null) $errors = new FormError();

        $TotaInfoDeFotos = 0;

        $userPdo = $app['pdo'](PdoMapper::PDO_USER);
        $user = $userPdo->get($this->sessionController->getSessionUserId($app));

        return $app['twig']->render('edit_profile.twig', array(
            'app'           => ['name' => $app['app.name']],
            'name'          => $this->sessionController->getSessionName($app),
            'birthday'      => $user->getBirthday(),
            'idUser'        => $this->sessionController->getSessionUserId($app),
            'haveProfileImage' => $user->getProfileImage(),
            'img'           => '/profile_img/' . $this->sessionController->getSessionUserId($app) . '.jpg',
            'logged'        => false,
            'data'          => $TotaInfoDeFotos,
            'errors'        => $errors
        ));
    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function renderValidation(Application $app)
    {
        return $app['twig']->render('validation.twig', array());
    }

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function renderUploadImage(Application $app, $errors = null)
    {
        if ($errors == null) $errors = new FormError();

        $idUser         = $this->sessionController->getSessionUserId($app);
        $profileImage   = $this->getProfileImage($app, $idUser);


        return $app['twig']->render('uploadImage.twig', array(
            'app'       => ['name' => $app['app.name']],
            'logged'    => $this->sessionController->haveSession($app),
            'profileImage' => $profileImage,
            'name'      => $this->sessionController->getSessionName($app),
            'errors'    => $errors

        ));

    }

    /**
     * @param Application $app
     * @param $id
     * @return mixed
     */
    public function renderImageView(Application $app, $id)
    {
        $imageViewController = new ImageViewController();

        $likesPdo = $app['pdo'](PdoMapper::PDO_IMAGE_LIKE);
        $commentsPdo = $app['pdo'](PdoMapper::PDO_COMMENT);

        $idUser = $this->sessionController->getSessionUserId($app);


        $user = $app['pdo'](PdoMapper::PDO_USER);
        $user->get($idUser);

        $image = $imageViewController->prepareImage($app, $id);
        if ($image != false) {

            $image->setLiked(!($likesPdo->likevalid($image->getId(), $this->sessionController->getSessionUserId($app))));


            $profileImage = $this->getProfileImage($app, $idUser);


//            //Image not found
//            if (!$image) {
//                return $app['twig']->render('error.twig', array(
//                    'message' => "Imagen no encontrada.",
//                ));
//            }

            $image->setNumComments($commentsPdo->getTotalImageComments($image->getId()));

            $dateFormatted = AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt()));

            //Image OK
            return $app['twig']->render('image-view.twig', array (
                'app'           => ['name' => $app['app.name']],
                'image'         => $image,
                'name'          => $user->getName($idUser),
                'profileImage'  => $profileImage,
                'logged'        => $idUser,
                'date'          => $dateFormatted
            ));
        } else { //Image not found
            $response = new Response();
            $content = $app['twig']->render('error.twig', array(
                'message' => "Imagen no encontrada"
            ));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN); // 403 code
            return $response;
        }
    }

    /**
     * @param Application $app
     * @param $id
     */
    public function renderUserProfile(Application $app, $id, $ordMode = 1, $currentUser = -1)
    {
        $profileImage = $this->getProfileImage($app, $id);
        $user = $this->getInfoUser($app, $id);

        $image = $this->getImagesUser($app, $id, $ordMode);

        $pdo = $app['pdo'](PdoMapper::PDO_IMAGE);

        $totalUserImages = $pdo->getTotalUserImages($id);


        $pdoFollow = $app['pdo'](PdoMapper::PDO_FOLLOW);

        if ($currentUser == -1 && $this->sessionController->haveSession($app))
            $currentUser = $this->sessionController->getSessionUserId($app);

        $followed = $pdoFollow->getIsFollowedBy($currentUser, $id);


        if ($this->sessionController->haveSession($app))
            $currentUser = $this->sessionController->getSessionUserId($app);

        //TODO FALTA QUE LAS IMAGENES SE PUEDAN FILTRAR

        return $app['twig']->render('user-profile.twig', array(
            'app' => ['name' => $app['app.name']],
            'idUser' => $id,
            'name' => $this->sessionController->getSessionName($app),
            'profileImg' => $profileImage,
            'logged' => $this->sessionController->getSessionUserId($app),
            'mail' => $user->getEmail(),
            'date' => $user->getBirthday(),
            'profileName' => $user->getUsername(),
            'comments' => $this->getUserComments($app, $id),
            'nImgs' => $totalUserImages,
            'images' => $image,
            'currentUserId' => $currentUser,
            'followed' => $followed

        ));
    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function renderUserImages(Application $app)
    {

        if ($this->sessionController->correctSession($app)) {

            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            $userUploadImagesController = new UserUploadImagesController();

            $images = $userUploadImagesController->getUserUploadImages($app, $idUser);

            $image = $this->getProfileImage($app, $idUser);
            if ($images != null) {
                return $app['twig']->render('user_images.twig', array(
                    'app' => ['name' => $app['app.name']],
                    'name' => $this->sessionController->getSessionName($app),
                    'img' => $profileImage,
                    'logged' => $idUser,
                    'images' => $images
                ));
            } else {
                return $app['twig']->render('homeWelcome.twig', array(
                    'app' => ['name' => $app['app.name']],
                    'name' => $this->sessionController->getSessionName($app),
                    'img' => $image,
                    'logged' => $idUser,
                    'p' => 'Aun no has subido ninguna foto',
                ));
            }
        }
    }

    /**
     * @param Application $app
     * @param $idImage
     * @param null $errors
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function renderEditImage(Application $app, $idImage, $errors = null)
    {

        if ($errors == null) $errors = new FormError();

        if ($this->sessionController->correctSession($app)) {

            $pdoImage = $app['pdo'](PdoMapper::PDO_IMAGE);

            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            $image = $pdoImage->get($idImage);

            if ($image->isPrivate()) {
                $private = 'checked';
            } else $private = '';

            return $app['twig']->render('edit-image.twig', array(
                'app' => ['name' => $app['app.name']],
                'name' => $this->sessionController->getSessionName($app),
                'img' => $profileImage,
                'logged' => $idUser,
                'image' => $image,
                'private' => $private,
                'errors' => $errors
            ));

        } else return $app->redirect('/login');

    }


    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function renderUserComments(Application $app)
    {

        if ($this->sessionController->correctSession($app)) {

            $commentsPdo    = $app['pdo'](PdoMapper::PDO_COMMENT);
            $userPdo        = $app['pdo'](PdoMapper::PDO_USER);
            $imagesPdo      = $app['pdo'](PdoMapper::PDO_IMAGE);
            $idUser         = $this->sessionController->getSessionUserId($app);


            //Images array that will be displayed on the main page
            $userImagesCommented = $imagesPdo->getAllImagesCommentedByAnUser($idUser);
            $userImagesCommented = !$userImagesCommented ? [] : $userImagesCommented; // if false, return an empty array, if not return the public images

            // let's add all the comments for each image
            foreach ($userImagesCommented as $image) {

                $comments = $commentsPdo->getImageCommentsFromUser($image->getId(), $idUser);

                if (!$comments) $comments = [];
                $image->setComments($comments);
                $userName = $userPdo->getName($image->getFkUser());
                $image->setUserName($userName);
            }

            $image = $this->getProfileImage($app, $idUser);

            if ($userImagesCommented != null) {

                return $app['twig']->render('userComments.twig', array(
                    'app' => ['name' => $app['app.name']],
                    'name' => $this->sessionController->getSessionName($app),
                    'img' => $image,
                    'idUser' => $idUser,
                    'images' => $userImagesCommented
                ));
            } else {
                return $app['twig']->render('homeWelcome.twig', array(
                    'app' => ['name' => $app['app.name']],
                    'name' => $this->sessionController->getSessionName($app),
                    'img' => $image,
                    'logged' => $idUser,
                    'p' => 'Aun no has hecho ningún comentario',
                    'images' => $userImagesCommented
                ));
            }
        } else return $app->redirect('/login');
        //TODO error 403

    }

    /**
     * @param Application $app
     * @param $idComment
     * @param $idImage
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function renderEditComment(Application $app, $idComment, $idImage)
    {

        if ($this->sessionController->correctSession($app)) {

            $imageViewController = new ImageViewController();

            $image = $imageViewController->prepareImage($app, $idImage);

            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            //Image not found
            if (!$image) {
                return $app['twig']->render('error.twig', array(
                    'message' => "Imagen no encontrada.",
                ));
            }

            //Image OK
            return $app['twig']->render('edit-comment.twig', array(
                'app' => ['name' => $app['app.name']],
                'image' => $image,
                'name' => $this->sessionController->getSessionName($app),
                'profileImage' => $profileImage,
                'logged' => $idUser,
                'userComment' => "",
                'idComment' => $idComment
            ));
        } else return $app->redirect('/login');
    }

    /**
     * @param \Exception $e
     * @param $code
     * @param $app
     * @return Response
     */
    public function renderUnknown(\Exception $e, $code, $app)
    {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array(
            'message' => "Error desconocido. Disculpe las molestias!"
        ));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN); // 403 code
        return $response;
    }


    /*
     * gets
     *
     */

    public function getProfileImage(Application $app, $idUser)
    {
        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);

        if ($pdoUser->getProfileImage($idUser)) {
            return $idUser;
        }
        return "img_profile_default";
    }

    public function getInfoUser(Application $app, $idUser)
    {
        $pdoUser = $app['pdo'](PdoMapper::PDO_USER);

        return $pdoUser->get($idUser);
    }

    public function getUserLikes(Application $app, $idUser)
    {
        $pdoUser = $app['pdo'](PdoMapper::PDO_IMAGE_LIKE);

        return $pdoUser->getTotalUserLikes($idUser);
    }

    public function getUserComments(Application $app, $idUser)
    {
        $pdoUser = $app['pdo'](PdoMapper::PDO_COMMENT);

        return $pdoUser->getTotalUserComments($idUser);
    }

    public function getPublicImages(Application $app)
    {
        $pdoImage   = $app['pdo'](PdoMapper::PDO_IMAGE);
        $pdoUser    = $app['pdo'](PdoMapper::PDO_USER);
        $pdoComment = $app['pdo'](PdoMapper::PDO_COMMENT);

        $publicImages = array();

        // Obtain all public images in db
        $imagesFromDB = $pdoImage->getAll();
        if ($imagesFromDB == 0) return false;

        foreach ($imagesFromDB as $imageFromDB) {

            if (!$imageFromDB['private']) {

                $image = new Image($imageFromDB['title'], $imageFromDB['created_at'], $imageFromDB['fk_user'], false, $imageFromDB['extension'],
                    $imageFromDB['visits'], $imageFromDB['likes'], $imageFromDB['id']);

                $userName = $pdoUser->getName($imageFromDB['fk_user']);
                $image->setUserName($userName);
                $image->setComments($pdoComment->getImageComments($image->getId()));
                $image->setNumComments($pdoComment->getTotalImageComments($image->getId()));

                array_push($publicImages, $image);
            }
        }
        return $publicImages;
    }

    public function getImagesUser(Application $app, $id, $ordMode)
    {
        $pdoImage = $app['pdo'](PdoMapper::PDO_IMAGE);

        // Obtain all public images in db
        $imagesFromDB = $pdoImage->getAllUserImagesNonPrivate($id, $ordMode);

        return $imagesFromDB;
    }

}