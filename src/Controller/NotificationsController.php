<?php
namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Notification;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class NotificationsController {



    public function getUserNotifications(Application $app) {

        $sessionController = new SessionController();

        $db = Database::getInstance("pwgram");
        $pdoNotifications = new PdoNotificationRepository($db);
        $pdoUser = new PdoUserRepository($db);


        $notifications = $pdoNotifications->getAllUserNotifications($app, $sessionController->getSessionUserId($app));

        // Add Username who caused notification into each notification
        $this->insertUsernameTitle($app, $notifications);

        return $notifications;
    }



    /**
     *
     */
    public function renderNotifications(Application $app) {
        //TODO: comprovar que esta la sesion

        $sessionController  = new SessionController();
        $render             = new RenderController();

        if($sessionController->correctSession($app)){


            $pdoNotifications = new NotificationsController();

            $userNotifications = $pdoNotifications->getUserNotifications($app);

            $idUser = $sessionController->getSessionUserId($app);
            $image = $render->getProfileImage($app, $idUser);

            if(sizeof($userNotifications) == 0) {
                return $app['twig']->render('homeWelcome.twig', array(
                    'app'=> ['name' => $app['app.name']],
                    'name'=> $sessionController->getSessionName($app),
                    'img'=> $image,
                    'logged'=> $sessionController->haveSession($app),
                    'p'=> ' Aún no tienes ninguna notificación '
                ));
            }



            $content = $app['twig']->render('notifications.twig',
                [   'name'      => $sessionController->getSessionName($app),
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


    /**
     * This function insert in each comment the username of the user who causes notification
     * and the title of the images where notification is associated.
     * @param $app
     * @param $notifications
     * @return mixed
     */
    public function insertUsernameTitle($app, $notifications) {

        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);
        $pdoImage = new PdoImageRepository($db);

        foreach($notifications as $notification) {
            $idUserFrom = $notification->getFrom();
            $username = $pdoUser->getName($app, $idUserFrom);

            $imgId = $notification->getWhere();
            $imgTitle = $pdoImage->getTitle($app, $imgId);

            $notification->setFromUsername($username);
           // if($imgTitle != null)
            $notification->setImgTitle($imgTitle);
        }

        return $notifications;
    }

    /**
     * Delete notification
     * @param $app
     * @param $id
     */
    public function deleteNotification(Application $app, $id) {
        $db = Database::getInstance("pwgram");
        $pdoNotification = new PdoNotificationRepository($db);

        $pdoNotification->remove($app, $id);

        return $app->redirect('/notifications');
    }

}