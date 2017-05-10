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
use Silex\Application;



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
    const TABLE_NAME    = "Comment";

    const APP_MAX_COMMENTS_PAGINATED = 3;

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
    public function add(Application $app, $row)
    {
        $result = $app['db']->insert(
            PdoCommentRepository::TABLE_NAME,
            array(
                'content'       =>  $row->getContent(),
                'last_modified' =>  $row->getLastModified(),
                'fk_user'       =>  $row->getFkUser(),
                'fk_image'      =>  $row->getFkImage()
            )
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
    public function get(Application $app, $id)
    {
        $query   = "SELECT id, content, last_modified, fk_user, fk_image FROM `Comment` WHERE id = ?";
        $comment = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );

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
     * @param int $id       The image foreign key
     * @param int $offset
     * @param int $limit
     *
     * @return false|mixed false if an error happened doing the request or an array of the
     *         comments associated with an image.
     */
    public function getImageComments(Application $app, $id, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM `Comment` WHERE fk_image = ? ORDER BY last_modified ASC LIMIT 3";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $id
                )
            );
        }
        else {

            $query = "SELECT * FROM `Comment` WHERE fk_image = ? ORDER BY last_modified ASC LIMIT ?, ?";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $id,
                    $offset,
                    $limit
                ),
                array(\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT)
            );
        }
        if (!$result) return false; // an error happened during the execution

        $resComments = [];

        foreach ($result as $comment) {

            array_push($resComments,
                new Comment(
                    $comment['content'],
                    $comment['fk_user'],
                    $comment['last_modified'],
                    $comment['fk_image'],
                    $comment['id']
                )
            );
        }

        return $resComments;
    }

    /**
     * @param Application $app
     * @param $id               The id of the image.
     * @param $idUser
     * @param int $offset
     * @param int $limit
     * @return array|bool
     */
    public function getImageCommentsFromUser(Application $app, $id, $idUser, $offset = 0, $limit = PdoRepository::MAX_RESULTS_LIMIT) {

        if ($offset == 0) {

            $query = "SELECT * FROM `Comment` WHERE fk_image = ? AND fk_user = ?  ORDER BY last_modified ASC";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $id,
                    $idUser
                )
            );
        }
        else {

            $query = "SELECT * FROM `Comment` WHERE fk_image = ? AND fk_user = ? ORDER BY last_modified ASC LIMIT ?, ?";
            $result = $app['db']->fetchAll(
                $query,
                array(
                    $id,
                    $idUser,
                    $offset,
                    $limit
                )
            );
        }
        if (!$result) return false; // an error happened during the execution

        return $this->populateComments($result);
    }

    /**
     * @param Application $app
     * @param int $id               The id of the user.
     * @return array|bool           false if an error happened, an array of comments if not.
     */
    public function getAllUserComments(Application $app, $id) {

        $query = "SELECT * FROM `Comment` WHERE fk_user = ? ORDER BY last_modified ASC";
        $result = $app['db']->fetchAll(
            $query,
            array(
                $id,
            )
        );

        if (!$result) return false; // an error happened during the execution

        return $this->populateComments($result);
    }

    /**
     * @param Application $app
     * @param int $id               The id of the user.
     * @return int              The number of comments made by an user.
     */
    public function getTotalUserComments(Application $app, $id) {

        $query  = "SELECT COUNT(*) as total FROM Comment WHERE fk_user = ?";
        $result = $comment = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );
        if (!$result) return 0;

        return $result['total'];
    }

    public function getTotalImageComments(Application $app, $id) {

        $query  = "SELECT COUNT(*) as total FROM Comment WHERE fk_image = ?";
        $result = $comment = $app['db']->fetchAssoc(
            $query,
            array(
                $id
            )
        );
        if (!$result) return 0;

        return $result['total'];
    }



    /**
     * Updates an existing comment with new information.
     *
     * @param Comment $row  The comment to be updated.
     */
    public function update(Application $app, $row)
    {
        $query = "UPDATE `Comment` SET content = ?, last_modified = ? WHERE id = ?";
        $result = $app['db']->executeUpdate(
            $query,
            array(
                $row->getContent(),
                $row->getLastModified(),
                $row->getId()
            )
        );
    }

    /**
     * Removes an existing comment from the database.
     *
     * @param int $id   The comment to be deleted.
     */
    public function remove(Application $app, $id)
    {
        $app['db']->delete(PdoCommentRepository::TABLE_NAME,
            array(
                'id' => $id
            ));
    }

    public function length(Application $app)
    {
        $result = $app['db']->executeQuery("SELECT COUNT(*) AS total FROM Comment");

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

    public function commentValid(Application $app, $idImage, $idUser) {
        $query = "SELECT id FROM `Comment` WHERE fk_image = ? AND fk_user = ?";
        $result = $app['db']->fetchAssoc(
            $query,
            array(
                $idImage,
                $idUser
            )
        );

        if (!$result) return true;

        return false; // User already put a comment
    }

    private function populateComments($queryResult) {

        $comments = [];

        foreach ($queryResult as $comment) {

            array_push(
                $comments,
                    new Comment(
                        $comment['content'],
                        $comment['fk_user'],
                        $comment['last_modified'],
                        $comment['fk_image'],
                        $comment['id']
                    )
                );
        }

        return $comments;
    }


}