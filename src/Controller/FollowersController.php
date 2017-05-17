<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 15/05/17
 * Time: 02:10
 */

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Follow;
use pwgram\Model\Repository\PdoFollowRepository;
use pwgram\Model\Repository\PdoUserRepository;
use pwgram\Model\Services\PdoMapper;
use Silex\Application;



class FollowersController
{

    /**
     * Creates the link from the follower to the followed user.
     *
     * @param Application $app
     * @param int $user             The user who has followed someone.
     * @param int $who              The user who has been followed.
     *
     * @return true if the follow is correct, false if not.
     */
    public function followUser(Application $app, $user, $who) {

        $follow = new Follow($user, $who);

        $pdoFollow = $app['pdo'](PdoMapper::PDO_FOLLOW);

        $pdoFollow->add($follow);

        $render = new RenderController();

        return $render->renderUserProfile($app,$who, 1, $user);
    }

    /**
     * Removes the link from the follower to the followed user.
     *
     * @param Application $app
     * @param int $user             The user who has unfollowed someone.
     * @param int $who              The user who has been unfollowed.
     *
     * @return true if the unfollow is correct, false if not.
     */
    public function unfollowUser(Application $app, $user, $who) {

        $pdoFollow = $app['pdo'](PdoMapper::PDO_FOLLOW);

        $follow = $pdoFollow->getIsFollowedBy($user, $who);

        if ($follow) $pdoFollow->remove($follow['id']);

        $render = new RenderController();

        return $render->renderUserProfile($app,$who, 1, $user);
    }


    /**
     * @param Application $app
     *
     * Returns all the users that the current user follows.
     *
     * Also, this method returns, for each user folowed, a list of users
     * that also follows this user that are also followed by the current user.
     *
     * @return mixed    The array of followers.
     */
    public function renderFollowsList(Application $app) {

        $sessionController  = new SessionController();
        $renderController   = new RenderController();

        $userId = $sessionController->getSessionUserId($app);

        $pdoFollow = $app['pdo'](PdoMapper::PDO_FOLLOW);
        $pdoUser   = $app['pdo'](PdoMapper::PDO_USER);

        $follows = $pdoFollow->getUserFollows($userId);

        $users = [];
        $followsProfileImages = [];


        $profileImage = $renderController->getProfileImage($app, $userId);

        $sharedFollowersPerUser= [];


        foreach ($follows as $follow) {

            $user = $pdoUser->get($follow->getFkFollows());
            $followImage = $renderController->getProfileImage($app, $follow->getFkFollows());

            $sharedFollowers = $pdoFollow->getSharedFollows($userId, $follow->getFkFollows());

            // to get the shared followers between the user and the follower

            array_push(
                $users,
                $user
            );

            array_push(
                $followsProfileImages,
                $followImage
            );

            array_push(
                $sharedFollowersPerUser,
                $this->getSharedFollowersList($sharedFollowers, $pdoUser)
            );
        }

        return $app['twig']->render('follows-list.twig',
            array(

                'app'                   => ['name' => $app['app.name']],
                'idUser'                => $userId,
                'name'                  => $sessionController->getSessionName($app),
                'img'                   => $profileImage,
                'users'                 => $users,
                'user_profile_images'   => $followsProfileImages,
                'shared_followers'      => $sharedFollowersPerUser
            ));
    }

    private function getSharedFollowersList($sharedFollowers, $pdoUser) {
        $sharedUserList = [];

        foreach ($sharedFollowers as $shared) {

            $userShared = $pdoUser->get($shared->getFkUser());

            array_push(
                $sharedUserList,
                $userShared
            );
        }

        return $sharedUserList;
    }

}