<?php


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use pwgram\Controller\SessionController;


// TODO cambiar de sitio
$sessionControl = function (Request $request,Application $app) {
    if (!$app['session']->has('user')){

        $response = new Response();
        $content =  $app['twig']->render('error.twig',array(
            'message'=>"Hace falta estar logeado"
        ));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN); // 403 code
        return $response;

    }
};


$app->post('/login/form', 'pwgram\\Controller\\FormsUserController::loginUser');

$app->post('/register/form', 'pwgram\\Controller\\FormsUserController::registerUser');

$app->post('/edit_profile/form', 'pwgram\\Controller\\FormsUserController::updateUser')->before($sessionControl);

$app->post('/uploadImage/form', 'pwgram\\Controller\\FormsImageController::uploadImage')->before($sessionControl);

$app->post('/new-comment/{id}', 'pwgram\\Controller\\CommentsController::addComment')->before($sessionControl);

$app->get('', 'pwgram\\Controller\\RenderController::renderHome');

/*Basics, no logged user*/
$app->get('/login', 'pwgram\\Controller\\RenderController::renderLogin');
$app->get('/register', 'pwgram\\Controller\\RenderController::renderRegistration');

/*Account verification*/
$app->get('/validation', 'pwgram\\Controller\\RenderController::renderValidation');
$app->get('/dovalidation/{id}', 'pwgram\\Controller\\ValidationController::userValidation');

/*Menu routes, logged user*/
$app->get('/logout', 'pwgram\\Controller\\RenderController::logout');
$app->get('/upload-image', 'pwgram\\Controller\\RenderController::renderUploadImage')->before($sessionControl);



$app->get('/edit-profile', 'pwgram\\Controller\\RenderController::renderEditProfile')->before($sessionControl);

$app->get('/editImage', 'pwgram\\Controller\\RenderController::renderHome')->before($sessionControl);
$app->get('/profile', 'pwgram\\Controller\\RenderController::renderHome');

/*Comments*/
$app->get('/new-comment/{id}', 'pwgram\\Controller\\CommentsController::addComment')->before($sessionControl);
$app->get('/user-comments', 'pwgram\\Controller\\RenderController::renderUserComments')->before($sessionControl);
$app->get('/edit-comment/{idComment}/{idImage}', 'pwgram\\Controller\\RenderController::renderEditComment')->before($sessionControl);
$app->get('/delete-comment/{idComment}/{idImage}', 'pwgram\\Controller\\CommentsController::deleteComment')->before($sessionControl);
$app->get('/edit-user-comment/form/{idComment}', 'pwgram\\Controller\\CommentsController::editComment')->before($sessionControl);
$app->post('/image-more-comments/{idImage}/{lastComment}', 'pwgram\\Controller\\CommentsController::onShowMoreComments');

/*Like*/
$app->get('/like/{id}', 'pwgram\\Controller\\LikesController::addLike')->before($sessionControl);

/*Image View*/
$app->get('/image-view/{id}', 'pwgram\\Controller\\RenderController::renderImageView');

/*User Profile*/
$app->get('/user-profile/{id}/{ordMode}','pwgram\\Controller\\RenderController::renderUserProfile');

$app->get('/user-images/', 'pwgram\\Controller\\RenderController::renderUserImages')->before($sessionControl);

$app->get('/edit-image/{idImage}', 'pwgram\\Controller\\RenderController::renderEditImage')->before($sessionControl);
$app->get('/delete-image/{idImage}', 'pwgram\\Controller\\FormsImageController::deleteImage')->before($sessionControl);
$app->post('/editImage/form/{idImage}', 'pwgram\\Controller\\FormsImageController::editImageForm')->before($sessionControl);

/* Notifications */
$app->get('/notifications', 'pwgram\\Controller\\RenderController::renderNotifications')->before($sessionControl);
$app->get('/delete-notification/{id}','pwgram\\Controller\\NotificationsController::deleteNotification')->before($sessionControl);

/* Home */
$app->post('/home-more-images/{lastImage}', 'pwgram\\Controller\\HomeController::onShowMoreImages');
$app->get('/last-posts', 'pwgram\\Controller\\RenderController::renderHome');
$app->get('/most-visited', 'pwgram\\Controller\\RenderController::renderMostVisited');


/* Follows */
$app->get('/follow-user/{user}/{who}', 'pwgram\\Controller\\FollowersController::followUser')->before($sessionControl);
$app->get('/unfollow-user/{user}/{who}', 'pwgram\\Controller\\FollowersController::unfollowUser')->before($sessionControl);
$app->get('/followers-posts', 'pwgram\\Controller\\FollowersController::renderFollowsList')->before($sessionControl);


// TODO: DESCOMENTAR!!!!
//$app->error(function (\Exception $e, $code) use ($app) {
//
//    $response = new Response();
//    $content =  $app['twig']->render('error.twig',array(
//        'message'=>"Contenido no disponible. Disculpe las molestias."
//    ));
//    $response->setContent($content);
//    $response->setStatusCode(Response::HTTP_FORBIDDEN); // 403 code
//    return $response;
//});




