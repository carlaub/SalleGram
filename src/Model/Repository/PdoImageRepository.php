<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 13/04/17
 * Time: 20:02
 */

namespace pwgram\Model\Repository;


use Doctrine\DBAL\Connection;
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
    const TABLE_NAME            = "Image";
    const APP_MAX_IMG_PAGINATED = 5;

    /**
     * @var Database class instance.
     */
    private $db;


    /**
     * PdoImageRepository constructor.
     * @param Database|Connection $db
     */
    public function __construct($db)
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
    public function add($row)
    {

        //$query  = "INSERT INTO `Image`(`title`, `visits`, `private`, `created_at`, `likes`, `fk_user`) VALUES(?, ?, ?, ?, ?, ?)";
        $this->db->insert(PdoImageRepository::TABLE_NAME,
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
    public function incrementVisits($id) {

        $this->db->beginTransaction();

        try {

            $image = $this->get($id);
            if (!$image) {              // I think this should never happen, but I am not sure

                echo "The operation could not be done, error getting the image from the database.";
                $this->db->commitTransaction();
                exit;
            }

            $image->setVisits($image->getVisits() + 1);

            $this->update($image);
            $this->db->commit();
        }
        catch (Exception $e) {

            $this->db->rollBack();
        }
    }

    /**
     * This method updates the number of likes of a photo. Normally, this method should
     * be used to update the number of likes by 1 or -1. By default, increments the number
     * of likes by 1. The method runs in transaction mode to avoid concurrency problems.
     *
     * @param int $id      The id of the image to update.
     * @param int $inc     Can be <b>negative</b>.
     */
    public function updateLikes($id, $inc = 1) {

        $this->db->beginTransaction();

        try {

            $image = $this->get($id);
            if (!$image) {              // i think this should never happen, but I am not sure

                echo "The operation could not be done, error getting the image from the database.";
                exit;
            }

            $image->setLikes($image->getLikes() + $inc);

            $this->update($image);
            $this->db->commit();
        }
        catch (Exception $e) {

            $this->db->rollBack();
        }
    }


    /**
     * @param int $id     The id of the image requested.
     *
     * @return bool|Image An Image instance is returned if everything goes well,
     *                    false if the image could not be found.
     */
    public function get($id)
    {
        $query  = "SELECT * FROM `Image` WHERE id = ?";
        $image = $this->db->fetchAssoc(
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
            $image['visits'],
            $image['likes'],
            $image['id']
        );
    }

    public function getAll() {
        $query = "SELECT * FROM Image";
        $result =$this->db->fetchAll($query);

        if(!$result) return 0; // Any image in DB

        return $result;
    }


    public function getLastInsertedId() {
        $query = "SELECT LAST_INSERT_ID() as id";
        $result = $this->db->fetchAssoc($query);

        if (!$result) return false;

        return $result['id'];
    }

    public function getAllPublicImages($offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY created_at DESC LIMIT ?";
            $result =$this->db->fetchAll(
                $query,
                array(
                    (int) $limit
                ),
                array (\PDO::PARAM_INT)
            );
        }
        else {
            $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY created_at DESC LIMIT ?, ?";
            $result = $this->db->fetchAll(
                $query,
                array(
                    (int) $offset,
                    (int) $limit
                ),
                array(\PDO::PARAM_INT, \PDO::PARAM_INT)
            );
        }

        if (!$result) return []; // Any image in DB

        return $this->populateImages($result);
    }

    public function getTotalOfPublicImages() {

        $result = $this->db->executeQuery("SELECT COUNT(*) AS total FROM Image WHERE private IS FALSE");

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

    /**
     * @param int $id       The id of the user.
     * @param int $offset
     * @param int $limit
     * @return array|bool
     */
    public function getAllUserImages($id, $ordMode = 1, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            // Sort pictures according to likes, visits, comments or data
            switch($ordMode) {
                case 1:
                    // Ord by data
                    $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY created_at DESC";
                    break;
                case 2:
                    // Ord by likes
                    $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY likes DESC";
                    break;
                case 3:
                    // Ord by comments
                    $query = "SELECT Image.* , COUNT(Comment.id) AS post_count 
                              FROM Image  LEFT JOIN Comment ON Image.id = Comment.fk_image
                              WHERE Image.fk_user = ? 
                              GROUP BY Image.id
                              ORDER BY post_count DESC";
                    break;
                case 4:
                    // Ord by
                    $query = "SELECT * FROM Image WHERE fk_user = ?  ORDER BY visits DESC";
                    break;
            }


            $result = $this->db->fetchAll(
                $query,
                array(
                    $id
                )
            );
        }
        else {

            $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY created_at DESC LIMIT ?, ?";
            $result = $this->db->fetchAll(
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

    /**
     * @param int $id       The id of the user.
     * @param int $offset
     * @param int $limit
     * @return array|bool
     */
    public function getAllUserImagesNonPrivate($id, $ordMode = 1, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            // Sort pictures according to likes, visits, comments or data
            switch($ordMode) {
                case 1:
                    // Ord by data
                    $query = "SELECT * FROM Image WHERE fk_user = ? AND private = 0 ORDER BY created_at DESC";
                    break;
                case 2:
                    // Ord by likes
                    $query = "SELECT * FROM Image WHERE fk_user = ?  AND private = 0 ORDER BY likes DESC";
                    break;
                case 3:
                    // Ord by comments
                    $query = "SELECT Image.* , COUNT(Comment.id) AS post_count 
                              FROM Image  LEFT JOIN Comment ON Image.id = Comment.fk_image
                              WHERE Image.fk_user = ? AND private = 0
                              GROUP BY Image.id
                              ORDER BY post_count DESC";
                    break;
                case 4:
                    // Ord by
                    $query = "SELECT * FROM Image WHERE fk_user = ?  AND private = 0 ORDER BY visits DESC";
                    break;
            }


            $result = $this->db->fetchAll(
                $query,
                array(
                    $id
                )
            );
        }
        else {

            $query = "SELECT * FROM Image WHERE fk_user = ? AND private = 0 ORDER BY created_at DESC LIMIT ?, ?";
            $result = $this->db->fetchAll(
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

    public function getAllImagesCommentedByAnUser($id) {

        //SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = 1);
        $query = "SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = ?)";
        $result = $this->db->fetchAll(
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
    public function getTotalUserImages($id) {

        $query = "SELECT COUNT(*) as total FROM Image WHERE fk_user = ?";

        $result = $this->db->fetchAssoc(
            $query,
            array(
                $id
            )
        );

        if (!$result) return 0;

        return $result['total'];
    }

    public function getMostVisitedImages($max = 5) {

        $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY visits DESC LIMIT ?";

        $result = $this->db->fetchAll(
            $query,
            array(
                $max
            ),
            array(\PDO::PARAM_INT)
        );

        if(!$result) return []; // Any image in DB

        return $this->populateImages($result);
    }

    /**
     * Updates an existing image of the database.
     *
     * @param Image $row    An existing image with updated information.
     */
    public function update($row)
    {
        $query = "UPDATE `Image` SET title = ?, private = ?, likes = ?, visits = ? WHERE id = ?";
        $result = $this->db->executeUpdate(
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


    public function length()
    {
        $result = $this->db->executeQuery("SELECT COUNT(*) AS total FROM Image");

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
    public function getAuthor($id) {
        $query = "SELECT fk_user FROM Image WHERE id = ?";
        $result = $this->db->fetchAssoc(
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
    public function getTitle($id) {
        $query = "SELECT title FROM Image WHERE id = ?";
        $result = $this->db->fetchAssoc(
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
    public function remove($id)
    {
        $this->db->delete(PdoImageRepository::TABLE_NAME,
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

            if ($image['private'] === null) $image['private'] = false;

            array_push(
                $images,
                new Image(
                    $image['title'],
                    $image['created_at'],
                    $image['fk_user'],
                    $image['private'],
                    $image['visits'],
                    $image['likes'],
                    $image['id']
                )
            );
        }

        return $images;
    }


}