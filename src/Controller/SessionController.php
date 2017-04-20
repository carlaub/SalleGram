<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:23
 */

namespace pwgram\Controller;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Silex\Application;


/**
 * Class SessionController
 *
 * <p>The main purpose of this class is to check and manage a possible existing
 * session to know if a user is already connected in the system or not because
 * there are some features that needs the user to be connected to use them.</p>
 *
 * @package pwgram\Controller
 */
class SessionController
{


    /**
     * @param $app
     *
     * @return
     */
    public function getSessionUserId($app) {

        if ($this->haveSession($app)) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);
            $id = $pdoUser->validateUserSession($app['session']->get('user')['username'],
                $app['session']->get('user')['password']);

            if ($id != false) return $id;
        }
        return false;
    }
    /**
     *
     * TODO LLAMAR A ESTA FUCNION ANTES DE REENDERIZAR CUALQUIERA QUE NECESITE ESTAR LOGEADO ...
     * TODO ... SI DEVUELVE FALSE REENDERIZAR /LOGIN
     *
     * @param $app
     * @return bool
     */
    public function correctSession($app) {

        if ($app['session']->get('user') != null) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);
            if($pdoUser->validateUserLogin($app['session']->get('user')['username'],
                $app['session']->get('user')['password'])){
                return true;
            }
        }
        //TODO error 403
        return false;
    }


    public function haveSession(Application $app) {
        //var_dump($app['session']->get('user'));

        if ($app['session']->get('user') === null){
            return false;
        }
        return true;

    }

}