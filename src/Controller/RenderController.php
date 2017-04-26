<?php

namespace pwgram\Controller;

use Silex\Application;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\AppFormatDate;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;



class RenderController {

    private $sessionController;


    public function __construct()
    {
        $this->sessionController = new SessionController();
    }

    public function renderHome(Application $app) {
        //TODO: This is a first solution but must be rethinked for pagination

        $db = Database::getInstance("pwgram");
        $commentsPdo = new PdoCommentRepository($db);
        $userPdo     = new PdoUserRepository($db);
        $imagesPdo   = new PdoImageRepository($db);
        $likesPdo   = new PdoImageLikesRepository($db);


        //Images array that will be displayed on the main page
        $publicImages = $imagesPdo->getAllPublicImages($app);
        $publicImages = !$publicImages? [] : $publicImages; // if false, return an empty array, if not return the public images

        $imagesDatesFormatted   = [];

        // let's add all the comments for each image
        foreach ($publicImages as $image) {

            $comments = $commentsPdo->getImageComments($app, $image->getId());

            if (!$comments) $comments = [];
            $image->setComments($comments);

            $userName = $userPdo->getName($app, $image->getFkUser());
            $image->setUserName($userName);
            $image->setLiked(!($likesPdo->likevalid($app, $image->getId(), $this->sessionController->getSessionUserId($app))));

            array_push($imagesDatesFormatted, AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt())));
        }



        $image = $this->getProfileImage($app,$this->sessionController->getSessionUserId($app));


        if ($publicImages != null) {
            return $app['twig']->render('home.twig', array(
                'app'=> ['name' => $app['app.name']],
                'name'=> $this->sessionController->getSessionName($app),
                'img'=> $image,
                'logged'=> $this->sessionController->haveSession($app),
                'images'=>$publicImages,
                'dates' => $imagesDatesFormatted
            ));
        } else {
            return $app['twig']->render('homeWelcome.twig', array(
                'app'=> ['name' => $app['app.name']],
                'name'=> $this->sessionController->getSessionName($app),
                'img'=> $image,
                'logged'=> $this->sessionController->haveSession($app),
                'p'=> 'Sube fotos y compártelas con tus amigos ',
                'images'=>$publicImages,
                'dates' => $imagesDatesFormatted
            ));
        }

    }

    public function renderLogin(Application $app) {
        $TotaInfoDeFotos = 0;
        return $app['twig']->render('login.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>$this->sessionController->haveSession($app),
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderRegistration(Application $app) {
        $TotaInfoDeFotos = 0;
        return $app['twig']->render('register.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));
    }

    public function renderEditProfile(Application $app) {
        $TotaInfoDeFotos = 0;

        return $app['twig']->render('edit_profile.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }


    public function renderValidation(Application $app) {
        return $app['twig']->render('validation.twig', array(

        ));
    }


    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function renderUploadImage(Application $app) {
       /* if ($app['session']->get('user') === null){
            //TODO 403 code
           return $this->renderHome($app);
        }
        return $app['twig']->render('uploadImage.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>$this->haveSession($app),
        ));*/
       if ($this->sessionController->correctSession($app)){
           return $app['twig']->render('uploadImage.twig', array(
               'app'=> ['name' => $app['app.name']],
               'logged'=>$this->sessionController->haveSession($app),
           ));
       }
       return $app -> redirect('/login');
    }

    /**
     * @param Application $app
     * @param $id
     * @return mixed
     */
    public function renderImageView(Application $app, $id) {
        $imageViewController = new ImageViewController();
        $db = Database::getInstance("pwgram");
        $idUser = $this->sessionController->getSessionUserId($app);


        $user = new PdoUserRepository($db);
        $user->get($app, $idUser);

        $image = $imageViewController->prepareImage($app, $id);

        $profileImage = $this->getProfileImage($app, $idUser);


        //Image not found
        if (!$image) {
            return $app['twig']->render('error.twig',array(
                'message'=>"Imagen no encontrada.",
            ));
        }

        $dateFormatted = AppFormatDate::timeFromNowMessage(new \DateTime($image->getCreatedAt()));

        //Image OK
        return $app['twig']->render('image-view.twig', array(
            'app'=> ['name' => $app['app.name']],
            'image'=>$image,
            'name'=> $user->getName($app, $idUser),
            'profileImage'=> $profileImage,
            'logged'=> $idUser,
            'date' => $dateFormatted
        ));
    }

    /**
     * @param Application $app
     * @param $id
     */
    public function renderUserProfile(Application $app, $id) {

        $profileImage = $this->getProfileImage($app, $id);
        $user = $this->getInfoUser($app, $id);

        $image = $this->getImagesUser($app, $id);

        $pdo = new PdoImageRepository(Database::getInstance("pwgram"));
        $totalUserImages    = $pdo->getTotalUserImages($app, $id);

        //TODO FALTA QUE LAS IMAGENES SE PUEDAN FILTRAR

        return $app['twig']->render('user-profile.twig', array(
            'app'=> ['name' => $app['app.name']],
            'name'=> $this->sessionController->getSessionName($app),
            'profileImg'=> $profileImage,
            'logged'=> $this->sessionController->getSessionUserId($app),
            'mail'=> $user->getEmail(),
            'date'=> $user->getBirthday(),
            'profileName'=> $user->getUsername(),
            'comments'=>$this->getUserComments($app, $id),
            'nImgs'=> $totalUserImages,
            'images'=> $image

        ));
    }

    public function renderUserImages(Application $app) {


        if ($this->sessionController->correctSession($app)){
            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            $userUploadImagesController = new UserUploadImagesController();

            $images = $userUploadImagesController->getUserUploadImages($app, $idUser);


            return $app['twig']->render('user_images.twig', array(
                'app'=> ['name' => $app['app.name']],
                'name'=> $this->sessionController->getSessionName($app),
                'img'=> $profileImage,
                'logged'=> $idUser,
                'images'=> $images
            ));
        }


    }

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logout(Application $app) {
        $this->sessionController->closeSession($app);
        return $app -> redirect('/');
    }



    /**
     * @param $idUser
     * @return string
     */
    public function getProfileImage(Application $app, $idUser){
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);

        if($pdoUser->getProfileImage($app, $idUser)) {
            return $idUser;
        }
        return "img_profile_default";
    }

    public function getInfoUser(Application $app, $idUser){
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);

        return $pdoUser->get($app, $idUser);
    }

    public function getUserLikes(Application $app, $idUser){
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoImageLikesRepository($db);

        return $pdoUser->getTotalUserLikes($app, $idUser);
    }

    public function getUserComments(Application $app, $idUser){
        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoCommentRepository($db);

        return $pdoUser->getTotalUserComments($app, $idUser);
    }


    public function getPublicImages(Application $app) {
        $db = Database::getInstance("pwgram");
        $pdoImage   = new PdoImageRepository($db);
        $pdoUser    = new PdoUserRepository($db);
        $pdoComment = new PdoCommentRepository($db);

        $publicImages = array();

        // Obtain all public images in db
        $imagesFromDB =  $pdoImage->getAll($app);
        if ($imagesFromDB == 0) return false;

        foreach ($imagesFromDB as $imageFromDB) {

            if (!$imageFromDB['private']) {

                $image = new Image($imageFromDB['title'], $imageFromDB['created_at'], $imageFromDB['fk_user'], false, $imageFromDB['extension'],
                                    $imageFromDB['visits'], $imageFromDB['likes'], $imageFromDB['id']);

                $userName = $pdoUser->getName($app, $imageFromDB['fk_user']);
                $image->setUserName($userName);
                $image->setComments($pdoComment->getImageComments($app, $image->getId()));

                array_push($publicImages, $image);
            }
        }
        return $publicImages;
    }

    public function getImagesUser(Application $app,  $id){

        $db = Database::getInstance("pwgram");
        $pdoImage = new PdoImageRepository($db);
        $pdoUser = new PdoUserRepository($db);

        $publicImages = array();

        // Obtain all public images in db
        $imagesFromDB =  $pdoImage->getAllUserImages($app, $id);

        return $imagesFromDB;
    }

    public function renderEditImage(Application $app, $idImage){

        if ($this->sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");
            $pdoImage = new PdoImageRepository($db);


            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            $image = $pdoImage->get($app, $idImage);

            if($image->isPrivate()){
                $private = 'checked';
            }else $private = '';

            return $app['twig']->render('edit-image.twig', array(
                'app'=> ['name' => $app['app.name']],
                'name'=> $this->sessionController->getSessionName($app),
                'img'=> $profileImage,
                'logged'=> $idUser,
                'image'=> $image,
                'private'=> $private
            ));

        }else return $app -> redirect('/login');

    }

    /**
     *
     */
    public function renderNotifications(Application $app) {
        //TODO: comprovar que esta la sesion
        if($this->sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");

            $pdoNotifications = new NotificationsController();

            $userNotifications = $pdoNotifications->getUserNotifications($app);

            $idUser = $this->sessionController->getSessionUserId($app);
            $image = $this->getProfileImage($app, $idUser);

            $content = $app['twig']->render('notifications.twig',
                [   'name'      => $this->sessionController->getSessionName($app),
                    'img'       => $image,
                    'logged'    => $idUser,
                    'notifications' => $userNotifications
                ]);

            $response = new Response();
            $response->setStatusCode($response::HTTP_OK);
            $response->headers->set('Content-type', 'text/html');
            $response->setContent($content);

            return $response;

        }
        //TODO error 403
    }


    public function renderUserComments(Application $app) {

        if($this->sessionController->correctSession($app)){
            $db = Database::getInstance("pwgram");
            $commentsPdo = new PdoCommentRepository($db);
            $userPdo     = new PdoUserRepository($db);
            $imagesPdo   = new PdoImageRepository($db);
            $idUser = $this->sessionController->getSessionUserId($app);


            //Images array that will be displayed on the main page
            $userImagesCommented = $imagesPdo->getAllImagesCommentedByAnUser($app, $idUser);
            $userImagesCommented = !$userImagesCommented? [] : $userImagesCommented; // if false, return an empty array, if not return the public images

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
                    'app'=> ['name' => $app['app.name']],
                    'name'=> $this->sessionController->getSessionName($app),
                    'img'=> $image,
                    'idUser'=> $idUser,
                    'images'=>$userImagesCommented
                ));
            } else {
                return $app['twig']->render('homeWelcome.twig', array(
                    'app'=> ['name' => $app['app.name']],
                    'name'=> $this->sessionController->getSessionName($app),
                    'img'=> $image,
                    'logged'=> $idUser,
                    'p'=>'Aun no has hecho ningún comentario',
                    'images'=>$userImagesCommented
                ));
            }
        }else return $app -> redirect('/login');
        //TODO error 403

    }
    public function renderEditComment(Application $app, $idComment, $idImage){

        if($this->sessionController->correctSession($app)){

            $db = Database::getInstance("pwgram");
            $commentContent = new PdoCommentRepository($db);


            $imageViewController = new ImageViewController();

            $image = $imageViewController->prepareImage($app, $idImage);

            $idUser = $this->sessionController->getSessionUserId($app);
            $profileImage = $this->getProfileImage($app, $idUser);

            //Image not found
            if (!$image) {
                return $app['twig']->render('error.twig',array(
                    'message'=>"Imagen no encontrada.",
                ));
            }

            //Image OK
            return $app['twig']->render('edit-comment.twig', array(
                'app'=> ['name' => $app['app.name']],
                'image'=>$image,
                'name'=> $this->sessionController->getSessionName($app),
                'profileImage'=> $profileImage,
                'logged'=> $idUser,
                'userComment'=> "",
                'idComment'=> $idComment
            ));
        }else return $app -> redirect('/login');
    }


}

