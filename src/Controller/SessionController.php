<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:23
 */

namespace pwgram\Controller;

use pwgram\Model\Services\PdoMapper;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;





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

            $pdoUser = $app['pdo'](PdoMapper::PDO_USER);
            $id = $pdoUser->validateUserSession($app['session']->get('user')['id']);

            if ($id != false) return $id;
        }
        return false;
    }
    /**
     *
     * @param $app
     * @return bool
     */
    public function correctSession($app) {

        if ($this->haveSession($app)) {

            $pdoUser = $app['pdo'](PdoMapper::PDO_USER);
            if($pdoUser->validateUserSession($app['session']->get('user')['id'])){
                return true;
            }
        }
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

    public function getExistingError(Application $app) {

        return $app['session']->get('error');
    }

    public function setError(Application $app, $error) {

        return $app['session']->set('error', $error);
    }

    public function clearError(Application $app) {

        return $app['session']->set('error', null);
    }

    public function closeSession(Application $app){
        $app['session']->clear();
    }

    public function getSessionName(Application $app){

        $userPdo = $app['pdo'](PdoMapper::PDO_USER);

        if($this->haveSession($app)) return $userPdo->getName($this->getSessionUserId($app));
        else return false;
    }


    public static function sessionControl(Request $request, Application $app) {
        if (!$app['session']->has('user')){

            $response = new Response();
            $content =  $app['twig']->render('error.twig',array(
                'message'=>"Hace falta estar logeado"
            ));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN); // 403 code
            return $response;

        }
    }

    public function logout(Application $app)
    {
        $this->closeSession($app);
        return $app->redirect('/');
    }

}