<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 13/04/17
 * Time: 20:02
 */

namespace pwgram\Model\Repository;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Image;
use Silex\Application;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class PdoImageRepository
 *
 * <p>This class manages the Image table of <i>PWGRAM</i>. Any kind of change that is needed
 * to do to this table must be done using an instance from this class.</p>
 *
 * @author Carla Urrea
 * @author Jorge Melguizo
 * @author Albert Pernía
 *
 * @version 1.0
 *
 * @see PdoRepository
 *
 * @package pwgram\Model\Repository
 */
class PdoImageRepository implements PdoRepository
{
    const TABLE_NAME    = "Image";

    /**
     * @var Database class instance.
     */
    private $db;


    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Adds a new image into the database.
     *
     * @param Image $row    the new image to be added.
     *
     * @return bool         true if the image has been added correctly, false if not.
     */
    public function add(Application $app, $row)
    {

        //$query  = "INSERT INTO `Image`(`title`, `visits`, `private`, `created_at`, `likes`, `fk_user`) VALUES(?, ?, ?, ?, ?, ?)";
        $app['db']->insert(PdoImageRepository::TABLE_NAME,
            array(
                'title'         =>  $row->getTitle(),
                'visits'        =>  $row->getVisits(),
                'private'       =>  $row->isPrivate(),
                'created_at'    =>  $row->getCreatedAt(),
                'likes'         =>  $row->getLikes(),
                'fk_user'       =>  $row->getFkUser()
            )
        );

        //return $result !== false;
    }


    /**
     * Increments by one the number of visits of an image. This method runs in transaction mode
     * to avoid concurrency problems.
     *
     * @param int $id    The id of the image visited.
     */
    public function incrementVisits(Application $app, $id) {

        $this->db->initTransaction(); // some day this will work, trust in me

        $image = $this->get($app, $id);
        if (!$image) {              // I think this should never happen, but I am not sure

            echo "The operation could not be done, error getting the image from the database.";
            $this->db->commitTransaction();
            exit;
        }

        $image->setVisits($image->getVisits() + 1);

        $this->update($app, $image);
        $this->db->commitTransaction();
    }

    /**
     * This method updates the number of likes of a photo. Normally, this method should
     * be used to update the number of likes by 1 or -1. By default, increments the number
     * of likes by 1. The method runs in transaction mode to avoid concurrency problems.
     *
     * @param int $id      The id of the image to update.
     * @param int $inc     Can be <b>negative</b>.
     */
    public function updateLikes(Application $app, $id, $inc = 1) {

        $app['db']->beginTransaction();

        try {

            $image = $this->get($app, $id);
            if (!$image) {              // i think this should never happen, but I am not sure

                echo "The operation could not be done, error getting the image from the database.";
                //$app['db']->commit();
                exit;
            }

            $image->setLikes($image->getLikes() + $inc);

            $this->update($app, $image);
            $app['db']->commit();
        }
        catch (Exception $e) {

            $app['db']->rollBack();
        }
    }


    /**
     * @param int $id     The id of the image requested.
     *
     * @return bool|Image An Image instance is returned if everything goes well,
     *                    false if the image could not be found.
     */
    public function get(Application $app, $id)
    {
        $query  = "SELECT * FROM `Image` WHERE id = ?";
        $image = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );

        if (!$image) return false; // an error happened during the execution

        return new Image(
            $image['title'],
            $image['created_at'],
            $image['fk_user'],
            $image['private'],
            $image['extension'],
            $image['visits'],
            $image['likes'],
            $image['id']
        );
    }

    public function getAll(Application $app) {
        $query = "SELECT * FROM Image";
        $result = $app['db']->fetchAll($query);

        if(!$result) return 0; // Any image in DB

        return $result;
    }


    public function getLastInsertedId(Application $app) {
        $query = "SELECT LAST_INSERT_ID() as id";
        $result = $app['db']->fetchAssoc($query);

        if (!$result) return false;

        return $result['id'];
    }

    public function getAllPublicImages(Application $app, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY created_at";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $limit
                )
            );
        }
        else {
            $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY created_at DESC LIMIT ?, ?";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $offset,
                    $limit
                )
            );
        }

        if (!$result) return []; // Any image in DB

        return $this->populateImages($result);
    }

    /**
     * @param int $id       The id of the user.
     * @param int $offset
     * @param int $limit
     * @return array|bool
     */
    public function getAllUserImages(Application $app, $id, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY created_at DESC";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $id
                )
            );
        }
        else {

            $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY created_at DESC LIMIT ?, ?";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $id,
                    $offset,
                    $limit
                )
            );
        }

        if (!$result) return []; // Any image in DB

        return $this->populateImages($result);
    }

    public function getAllImagesCommentedByAnUser(Application $app, $id) {

        //SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = 1);
        $query = "SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = ?)";
        $result = $app['db']->fetchAll(
            $query,
            array(
                $id
            )
        );

        if (!$result) return []; // Any image in DB

        return $this->populateImages($result);
    }

    /**
     * @param Application $app
     * @param int $id               The id of the user.
     *
     * @return int                  The total of images of an user.
     */
    public function getTotalUserImages(Application $app, $id) {

        $query = "SELECT COUNT(*) as total FROM Image WHERE fk_user = ?";
        $result = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );

        if (!$result) return 0;

        return $result['total'];
    }

    public function getMostVisitedImages(Application $app, $max = 5) {

        $query = "SELECT * FROM Image ORDER BY visits ASC LIMIT ?";
        $result = $app['db']->fetchAll(
            $query,
            array(
                $max
            )
        );

        if(!$result) return []; // Any image in DB

        return $this->populateImages($result);
    }

    /**
     * Updates an existing image of the database.
     *
     * @param Image $row    An existing image with updated information.
     */
    public function update(Application $app, $row)
    {
        $query = "UPDATE `Image` SET title = ?, private = ?, likes = ?, visits = ? WHERE id = ?";
        $result = $app['db']->executeUpdate(
            $query,
            array(
                $row->getTitle(),
                $row->isPrivate(),
                $row->getLikes(),
                $row->getVisits(),
                $row->getId()
            )
        );
    }


    public function length(Application $app)
    {
        $result = $app['db']->executeQuery("SELECT COUNT(*) AS total FROM Image");

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

    /**
     * Return the image author
     * @param Application $app
     * @param $id
     * @return mixed
     */
    public function getAuthor(Application $app, $id) {
        $query = "SELECT fk_user FROM Image WHERE id = ?";
        $result = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );

        return $result['fk_user'];
    }

    /**
     * Return the image's title
     * @param Application $app
     * @param $id
     */
    public function getTitle(Application $app, $id) {
        $query = "SELECT title FROM Image WHERE id = ?";
        $result = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );

        return $result['title'];
    }


    /**
     * Deletes an existing image from the database.
     *
     * @param int $id   The id associated with the image to delete.
     */
    public function remove(Application $app, $id)
    {
        $app['db']->delete(PdoImageRepository::TABLE_NAME,
            array(
                'id' => $id
            ));
    }

    /**
     * @param $queryResult
     * @return array
     */
    private function populateImages($queryResult) {

        $images = [];

        foreach ($queryResult as $image) {

            array_push(
                $images,
                new Image(
                    $image['title'],
                    $image['created_at'],
                    $image['fk_user'],
                    $image['private'],
                    $image['extension'],
                    $image['visits'],
                    $image['likes'],
                    $image['id']
                )
            );
        }

        return $images;
    }


}