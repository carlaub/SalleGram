<?php
namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Notification;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;

use Silex\Application;

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
            $notification->setImgTitle($imgTitle);
        }

        return $notifications;
    }

    /**
     * Delete notification
     * @param $app
     * @param $id
     */
    public function deleteNotification($app, $id) {
        $db = Database::getInstance("pwgram");
        $pdoNotification = new PdoNotificationRepository($db);

        $pdoNotification->remove($app, $id);

        return $app->redirect('/notifications');
    }

}