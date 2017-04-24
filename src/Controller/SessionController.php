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



//TODO COOKIES



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
            $id = $pdoUser->validateUserSession($app, $app['session']->get('user')['id']);

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

        if ($this->haveSession($app)) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);
            if($pdoUser->validateUserSession($app, $app['session']->get('user')['id'])){
                return true;
            }
        }
        //TODO error 403
        return false;
    }


    public function haveSession(Application $app) {

        return $app['session']->has('user');
    }

    /**
     * @param Application $app
     * @param $userName
     * @param $dbPassword
     */
    public function setSession(Application $app, $userId) {

        // Only one session at the same time
        $this->closeSession($app);
        // Save the session
        $app['session']->set('user',  array('id' => $userId));
        $app['session']->start();



    }

    public function closeSession(Application $app){
        //TODO REVISAR FUNCION, parece que borra porque el $before de routes funciona pero en applications sale que hahy algo

        //$app['session']->remove('user');
        $app['session']->clear();
    }

    public function getSessionName(Application $app){

        $db = Database::getInstance("pwgram");
        $userPdo = new PdoUserRepository($db);

        if($this->haveSession($app)) return $userPdo->getName($app, $this->getSessionUserId($app));
        else return false;
    }

}