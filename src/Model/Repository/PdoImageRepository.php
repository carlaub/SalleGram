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
    public function add($row)
    {

        $query  = "INSERT INTO `Image`(`title`, `visits`, `private`, `created_at`, `likes`, `fk_user`) VALUES(?, ?, ?, ?, ?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getTitle(),
                $row->getVisits(),
                $row->isPrivate(),
                $row->getCreatedAt(),
                $row->getLikes(),
                $row->getFkUser()
            ]
        );

        return $result !== false;
    }


    /**
     * Increments by one the number of visits of an image. This method runs in transaction mode
     * to avoid concurrency problems.
     *
     * @param int $id    The id of the image visited.
     */
    public function incrementVisits($id) {

        $this->db->initTransaction();

        $image = $this->get($id);
        if (!$image) {              // I think this should never happen, but I am not sure

            echo "The operation could not be done, error getting the image from the database.";
            $this->db->commitTransaction();
            exit;
        }

        $image->setVisits($image->getVisits() + 1);

        $this->update($image);
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
    public function updateLikes($id, $inc = 1) {

        $this->db->initTransaction();

        $image = $this->get($id);
        if (!$image) {              // i think this should never happen, but I am not sure

            echo "The operation could not be done, error getting the image from the database.";
            $this->db->commitTransaction();
            exit;
        }

        $image->setLikes($image->getLikes() + $inc);

        $this->update($image);
        $this->db->commitTransaction();
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
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );

        if (!$result) return false; // an error happened during the execution

        $image = $result->fetch();

        if (!$image) return false;   // image not found

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

    public function getAll() {
        $query = "SELECT * FROM Image";
        $result = $this->db->query($query);

        if(!$result) return 0;

        $results = $result->fetchAll();

        if(!$results) return 0; // Any image in DB

        return $results;
    }


    public function getLastInsertedId() {
        $query = "SELECT LAST_INSERT_ID()";
        $result = $this->db->query($query);

        if (!$result) return false;

        $lastId = $result->fetch();

        return $lastId['LAST_INSERT_ID()'];
    }

    public function getAllPublicImages($offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY created_at";
            $result = $this->db->preparedQuery(
                $query,
                [
                    $limit
                ]
            );
        }
        else {
            $query = "SELECT * FROM Image WHERE private IS FALSE ORDER BY created_at DESC LIMIT ?, ?";
            $result = $this->db->preparedQuery(
                $query,
                [
                    $offset,
                    $limit
                ]
            );
        }

        if (!$result) return false;

        $results = $result->fetchAll();

        if(!$results) return []; // Any image in DB

        return $this->populateImages($results);
    }

    /**
     * @param int $id       The id of the user.
     * @param int $offset
     * @param int $limit
     * @return array|bool
     */
    public function getAllUserImages($id, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY created_at DESC";
            $result = $this->db->preparedQuery(
                $query,
                [
                    $id
                ]
            );
        }
        else {

            $query = "SELECT * FROM Image WHERE fk_user = ? ORDER BY created_at DESC LIMIT ?, ?";
            $result = $this->db->preparedQuery(
                $query,
                [
                    $id,
                    $offset,
                    $limit
                ]
            );
        }

        if (!$result) return false;

        $results = $result->fetchAll();

        if(!$results) return []; // Any image in DB

        return $this->populateImages($results);
    }

    public function getMostVisitedImages($max = 5) {

        $query = "SELECT * FROM Image ORDER BY visits ASC LIMIT ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $max
            ]
        );

        if (!$result) return false;

        $results = $result->fetchAll();

        if(!$results) return []; // Any image in DB

        return $this->populateImages($results);
    }

    /**
     * Updates an existing image of the database.
     *
     * @param Image $row    An existing image with updated information.
     */
    public function update($row)
    {
        $query = "UPDATE `Image` SET title = ?, visits = ?, private = ?, created_at = ?, likes = ?, fk_user = ? WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getTitle(),
                $row->getVisits(),
                $row->isPrivate(),
                $row->getCreatedAt(),
                $row->getLikes(),
                $row->getFkUser(),
                $row->getId()
            ]
        );
    }

    /**
     * Deletes an existing image from the database.
     *
     * @param int $id   The id associated with the image to delete.
     */
    public function remove($id)
    {
        $query = "DELETE FROM `Image` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
    }

    public function length()
    {
        $query = "SELECT COUNT(*) AS total FROM Image";
        $result = $this->db->query($query);

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }


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