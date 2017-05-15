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

        //$pdoFollow = $app['pdo'](PdoMapper::PDO_FOLLOW);

        $pdoFollow = new PdoFollowRepository();

        $ok = $pdoFollow->add($app, $follow);

        /*return json_encode(
                    array (
                        'follow_ok' =>  $ok
                    )
        );*/

        $render = new RenderController();

        return $render->renderUserProfile($app, $who, 1, $user);
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
        $unfollowed = false;

        //$pdoFollow = $app['pdo'](PdoMapper::PDO_FOLLOW);
        $pdoFollow = new PdoFollowRepository();

        $follow = $pdoFollow->getIsFollowedBy($app, $user, $who);

        if ($follow)
            $unfollowed = $pdoFollow->remove($app, $follow['id']);

        /*return json_encode(
                    array (
                        'unfollow_ok' =>  $unfollowed
                    )
        );*/

        $render = new RenderController();


        return $render->renderUserProfile($app, $who, 1, $user);

    }

    public function renderFollowsList(Application $app) {

        $sessionController  = new SessionController();
        $renderController   = new RenderController();

        $userId = $sessionController->getSessionUserId($app);

        $pdoFollow = new PdoFollowRepository();
        $pdoUser   = new PdoUserRepository(Database::getInstance('pwgram'));

        $follows = $pdoFollow->getUserFollows($app, $userId);

        $users = [];
        $followsProfileImages = [];


        $profileImage = $renderController->getProfileImage($app, $userId);

        $sharedFollowersPerUser= [];


        foreach ($follows as $follow) {

            $sharedUserList = [];

            $user = $pdoUser->get($app, $follow->getFkFollows());
            $followImage = $renderController->getProfileImage($app, $follow->getFkFollows());


            //var_dump($follow->getFkFollows());
            $sharedFollowers = $pdoFollow->getSharedFollows($app, $userId, $follow->getFkFollows());

            //var_dump($sharedUserList);
            foreach ($sharedFollowers as $shared) {

                $userShared = $pdoUser->get($app, $shared->getFkFollows());

                array_push(
                    $sharedUserList,
                    $userShared
                );
            }

            //var_dump($sharedUserList);

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
                $sharedUserList
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

}