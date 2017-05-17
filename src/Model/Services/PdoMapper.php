<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 15/05/17
 * Time: 02:31
 */

namespace pwgram\Model\Services;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use pwgram\Model\Repository\PdoCommentRepository;
use pwgram\Model\Repository\PdoFollowRepository;
use pwgram\Model\Repository\PdoImageLikesRepository;
use pwgram\Model\Repository\PdoImageRepository;
use pwgram\Model\Repository\PdoNotificationRepository;
use pwgram\Model\Repository\PdoUserRepository;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Class PdoMapper
 *
 * This Service is used to return created Pdo instances.
 *
 * @package pwgram\Model\Services
 */
class PdoMapper implements ServiceProviderInterface
{

    const PDO_FOLLOW        = 1;
    const PDO_USER          = 2;
    const PDO_IMAGE         = 3;
    const PDO_COMMENT       = 4;
    const PDO_IMAGE_LIKE    = 5;
    const PDO_NOTIFICATION  = 6;




    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app A container instance
     *
     * @return PdoCommentRepository|PdoFollowRepository|PdoImageLikesRepository|PdoImageRepository|PdoNotificationRepository|PdoUserRepository
     */
    public function register(Container $app)
    {

        $app['pdo'] = $app->protect(function ($pdoRepository) use ($app) {

            return $this->getPdoReference($pdoRepository, $app['db']);
        });
    }

    /**
     * Returns a Pdo Class instance.
     *
     *
     * @param int $pdoRepository    The Pdo class to return.
     * @param $db
     *
     * @return PdoCommentRepository|PdoFollowRepository|PdoImageLikesRepository|PdoImageRepository|PdoNotificationRepository|PdoUserRepository
     */
    public function getPdoReference($pdoRepository, $db) {

        switch ($pdoRepository) {

            case PdoMapper::PDO_FOLLOW:

                return new PdoFollowRepository($db);

            case PdoMapper::PDO_USER:

                return new PdoUserRepository($db);

            case PdoMapper::PDO_IMAGE:

                return new PdoImageRepository($db);

            case PdoMapper::PDO_COMMENT:

                return new PdoCommentRepository($db);

            case PdoMapper::PDO_IMAGE_LIKE:

                return new PdoImageLikesRepository($db);

            case PdoMapper::PDO_NOTIFICATION:

                return new PdoNotificationRepository($db);
        }

        throw new Exception("Illegal Argument Exception");
    }
}