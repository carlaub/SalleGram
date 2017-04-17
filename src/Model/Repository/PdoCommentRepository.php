<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 11/04/17
 * Time: 21:20
 */

namespace pwgram\Model\Repository;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Comment;



/**
 * Class PdoCommentRepository
 *
 * <p>This class manages the Comment table of <i>PWGRAM</i>. Any kind of change that is needed
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
class PdoCommentRepository implements PdoRepository
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
     * Adds a new comment to the database.
     *
     * @param Comment $row  The new comment to be added.
     *
     * @return bool         true if the comment has been added correctly, false if not.
     */
    public function add($row)
    {
        $query  = "INSERT INTO Comment(`content`, `last_modified`, `fk_user`, `fk_image`) VALUES(?, ?, ?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getContent(),
                $row->getLastModified(),
                $row->getFkUser(),
                $row->getFkImage()
            ]
        );

        return $result !== false;
    }


    /**
     * Gets an existing comment from the database.
     *
     * @param int $id   The id of the comment to find.
     *
     * @return bool|Comment An instance of Comment if it has been found, false if not.
     */
    public function get($id)
    {
        $query  = "SELECT id, content, last_modified, fk_user, fk_image FROM `Comment` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $comment = $result->fetch();

        if (!$comment) return false;   // comment not found

        return new Comment(
            $comment['content'],
            $comment['last_modified'],
            $comment['fk_user'],
            $comment['fk_image'],
            $comment['id']
        );
    }

    /**
     * @param int $id   The image foreign key
     *
     * @return false|mixed false if an error happened doing the request or an array of the
     *         comments associated with an image.
     */
    public function getImageComments($id) {

        $query  = "SELECT * FROM `Comment` WHERE fk_image = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $comments = $result->fetchAll();

        if (!$comments) return false;

        $resComments = [];

        foreach ($comments as $comment) {

            array_push($resComments,
                new Comment(
                    $comment['content'],
                    $comment['last_modified'],
                    $comment['fk_user'],
                    $comment['fk_image'],
                    $comment['id']
                )
            );
        }

        return $resComments;
    }
    /**
     * Updates an existing comment with new information.
     *
     * @param Comment $row  The comment to be updated.
     */
    public function update($row)
    {
        $query = "UPDATE `Comment` SET content = ?, last_modified = ? WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getContent(),
                $row->getLastModified(),
                $row->getId()
            ]
        );
    }

    /**
     * Removes an existing comment from the database.
     *
     * @param int $id   The comment to be deleted.
     */
    public function remove($id)
    {
        $query = "DELETE FROM `Comment` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
    }

    public function length()
    {
        $query = "SELECT COUNT(*) AS total FROM Comment";
        $result = $this->db->query($query);

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }
}