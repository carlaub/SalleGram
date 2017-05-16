<?php

namespace pwgram\Controller;

use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoFollowRepository;
use Silex\Application;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\AppFormatDate;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
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

        $db = Database::getInstance("pwgram");
        $commentsPdo = new PdoCommentRepository($db);
        $userPdo = new PdoUserRepository($db);
        $imagesPdo = new PdoImageRepository($db);
        $likesPdo = new PdoImageLikesRepository($db);


        if ($publicImages == null) {

            //Images array that will be displayed on the main page
            $publicImages = $imagesPdo->getAllPublicImages($app, 0, PdoImageRepository::APP_MAX_IMG_PAGINATED);
            $publicImages = !$publicImages ? [] : $publicImages; // if false, return an empty array, if not return the public images
        }
        $imagesDatesFormatted = [];

        // let's add all the comments for each image
        foreach ($publicImages as $image) {

            $comments = $commentsPdo->getImageComments($app, $image->getId(), 0, 3);
            //Set the name of username of the comment
            if (!$comments) $comments = [];
            else {
                foreach ($comments as $commentUser) {

                    $commentUser->setUserName($userPdo->getName($app, $commentUser->getFkUser()));
                    $commentUser->setFkUser(($this->getProfileImage($app, $commentUser->getFkUser())));//reutilitzo fk user per posar la foto
                }
            }

            $image->setComments($comments);

            $userName = $userPdo->getName($app, $image->getFkUser());
            $image->setUserName($userName);
            $image->setLiked(!($likesPdo->likevalid($app, $image->getId(), $this->sessionController->getSessionUserId($app))));

            $image->setNumComments($commentsPdo->getTotalImageComments($app, $image->getId()));

            array_push($imagesDatesFormatted, AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt())));
        }


        $image = $this->getProfileImage($app, $this->sessionController->getSessionUserId($app));
        if ($error == null) $error = new FormError();

        if ($publicImages != null) {
            return $app['twig']->render('home.twig', array(
                'app' => ['name' => $app['app.name']],
                'name' => $this->sessionController->getSessionName($app),
                'img' => $image,
                'logged' => $this->sessionController->haveSession($app),
                'images' => $publicImages,
                'dates' => $imagesDatesFormatted,
                'is_most_visited_layout' => $mostVisitedLayout,
                'errors'=> $error
            ));
        } else {
            return $app['twig']->render('homeWelcome.twig', array(
                'app' => ['name' => $app['app.name']],
                'name' => $this->sessionController->getSessionName($app),
                'img' => $image,
                'logged' => $this->sessionController->haveSession($app),
                'p' => 'Sube fotos y compártelas con tus amigos ',
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

        $db = Database::getInstance("pwgram");
        $imagesPdo = new PdoImageRepository($db);

        $imagesPdo = $imagesPdo->getMostVisitedImages($app);

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
            'app' => ['name' => $app['app.name']],
            'logged' => $this->sessionController->haveSession($app),
            'data' => $TotaInfoDeFotos,
            'errors' => $errors
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
            'app' => ['name' => $app['app.name']],
            'logged' => false,
            'data' => $TotaInfoDeFotos,
            'errors' => $errors,
            'user' => $user
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
        $db = Database::getInstance("pwgram");
        $userPdo = new PdoUserRepository($db);
        $user = $userPdo->get($app, $this->sessionController->getSessionUserId($app));

        return $app['twig']->render('edit_profile.twig', array(
            'app' => ['name' => $app['app.name']],
            'name' => $this->sessionController->getSessionName($app),
            'birthday' => $user->getBirthday(),
            'idUser' => $this->sessionController->getSessionUserId($app),
            'haveProfileImage' => $user->getProfileImage(),
            'img' => '/profile_img/' . $this->sessionController->getSessionUserId($app) . '.jpg',
            'logged' => false,
            'data' => $TotaInfoDeFotos,
            'errors' => $errors
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

        $idUser = $this->sessionController->getSessionUserId($app);
        $profileImage = $this->getProfileImage($app, $idUser);


        return $app['twig']->render('uploadImage.twig', array(
            'app' => ['name' => $app['app.name']],
            'logged' => $this->sessionController->haveSession($app),
            'profileImage' => $profileImage,
            'name' => $this->sessionController->getSessionName($app),
            'errors' => $errors

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
        $db = Database::getInstance("pwgram");
        $likesPdo = new PdoImageLikesRepository($db);
        $commentsPdo = new PdoCommentRepository($db);

        $idUser = $this->sessionController->getSessionUserId($app);


        $user = new PdoUserRepository($db);
        $user->get($app, $idUser);

        $image = $imageViewController->prepareImage($app, $id);
        if ($image != false) {

            $image->setLiked(!($likesPdo->likevalid($app, $image->getId(), $this->sessionController->getSessionUserId($app))));


            $profileImage = $this->getProfileImage($app, $idUser);


//            //Image not found
//            if (!$image) {
//                return $app['twig']->render('error.twig', array(
//                    'message' => "Imagen no encontrada.",
//                ));
//            }

            $image->setNumComments($commentsPdo->getTotalImageComments($app, $image->getId()));

            $dateFormatted = AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt()));

            //Image OK
            return $app['twig']->render('image-view.twig', array(
                'app' => ['name' => $app['app.name']],
                'image' => $image,
                'name' => $user->getName($app, $idUser),
                'profileImage' => $profileImage,
                'logged' => $idUser,
                'date' => $dateFormatted
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

        $pdo = new PdoImageRepository(Database::getInstance("pwgram"));

        $totalUserImages = $pdo->getTotalUserImages($app, $id);


        $pdoFollow = new PdoFollowRepository();

        if ($currentUser == -1 && $this->sessionController->haveSession($app))
            $currentUser = $this->sessionController->getSessionUserId($app);

        $followed = $pdoFollow->getIsFollowedBy($app, $currentUser, $id);


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
            $db = Database::getInstance("pwgram");
            $pdoImage = new PdoImageRepository($db);


            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            $image = $pdoImage->get($app, $idImage);

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
            $db = Database::getInstance("pwgram");
            $commentsPdo = new PdoCommentRepository($db);
            $userPdo = new PdoUserRepository($db);
            $imagesPdo = new PdoImageRepository($db);
            $idUser = $this->sessionController->getSessionUserId($app);


            //Images array that will be displayed on the main page
            $userImagesCommented = $imagesPdo->getAllImagesCommentedByAnUser($app, $idUser);
            $userImagesCommented = !$userImagesCommented ? [] : $userImagesCommented; // if false, return an empty array, if not return the public images

            // let's add all the comments for each image
            foreach ($userImagesCommented as $image) {

                $comments = $commentsPdo->getImageCommentsFromUser($app, $image->getId(), $idUser);

                if (!$comments) $comments = [];
                $image->setComments($comments);
                $userName = $userPdo->getName($app, $image->getFkUser());
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

            $db = Database::getInstance("pwgram");
            $commentContent = new PdoCommentRepository($db);


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
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);

        if ($pdoUser->getProfileImage($app, $idUser)) {
            return $idUser;
        }
        return "img_profile_default";
    }

    public function getInfoUser(Application $app, $idUser)
    {
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);

        return $pdoUser->get($app, $idUser);
    }

    public function getUserLikes(Application $app, $idUser)
    {
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoImageLikesRepository($db);

        return $pdoUser->getTotalUserLikes($app, $idUser);
    }

    public function getUserComments(Application $app, $idUser)
    {
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoCommentRepository($db);

        return $pdoUser->getTotalUserComments($app, $idUser);
    }

    public function getPublicImages(Application $app)
    {
        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);
        $pdoUser = new PdoUserRepository($db);
        $pdoComment = new PdoCommentRepository($db);

        $publicImages = array();

        // Obtain all public images in db
        $imagesFromDB = $pdoImage->getAll($app);
        if ($imagesFromDB == 0) return false;

        foreach ($imagesFromDB as $imageFromDB) {

            if (!$imageFromDB['private']) {

                $image = new Image($imageFromDB['title'], $imageFromDB['created_at'], $imageFromDB['fk_user'], false, $imageFromDB['extension'],
                    $imageFromDB['visits'], $imageFromDB['likes'], $imageFromDB['id']);

                $userName = $pdoUser->getName($app, $imageFromDB['fk_user']);
                $image->setUserName($userName);
                $image->setComments($pdoComment->getImageComments($app, $image->getId()));
                $image->setNumComments($pdoComment->getTotalImageComments($app, $image->getId()));

                array_push($publicImages, $image);
            }
        }
        return $publicImages;
    }

    public function getImagesUser(Application $app, $id, $ordMode)
    {

        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);
        $pdoUser = new PdoUserRepository($db);

        $publicImages = array();

        // Obtain all public images in db
        $imagesFromDB = $pdoImage->getAllUserImagesNonPrivate($app, $id, $ordMode);

        return $imagesFromDB;
    }

}