<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 15/05/17
 * Time: 02:15
 */

namespace pwgram\Model\Repository;


use pwgram\Model\Entity\Follow;
use Silex\Application;

class PdoFollowRepository implements PdoRepository
{

    const TABLE_NAME    = "Follow";


    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Adds a follow into the database.
     *
     * @param Application $app
     * @param Follow $row
     */
    public function add($row)
    {

        $insert = $this->db->insert(PdoFollowRepository::TABLE_NAME,
            array(
                'fk_user'       =>  $row->getFkUser(),
                'fk_follows'    =>  $row->getFkFollows(),
            ));

        return $insert;
    }

    public function get($id)
    {
        $query  = "SELECT * FROM `Follow` WHERE id = ?";
        $follow = $this->db->fetchAssoc($query, array($id));

        if (!$follow) return false; // an error happened during the execution

        return new Follow(
            $follow['fk_user'],
            $follow['fk_follows'],
            $follow['id']
        );
    }

    public function getIsFollowedBy($follower, $followed) {

        $query  = "SELECT * FROM `Follow` WHERE fk_user = ? AND fk_follows = ?";
        $follow = $this->db->fetchAssoc(
                        $query,
                        array($follower, $followed),
                        array(\PDO::PARAM_INT, \PDO::PARAM_INT));

        return $follow;
    }

    public function getUserFollows($id) {

        //SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = 1);
        $query = "SELECT * FROM Follow WHERE fk_user = ?";
        $result = $this->db->fetchAll(
            $query,
            array(
                $id
            ),
            array(\PDO::PARAM_INT)
        );

        if (!$result) return []; // Any image in DB

        return $this->populateFollows($result);
    }

    /**
     * Returns all the users that follows a user A that are also followed by a specific user B.
     *
     * @param Application $app
     * @param int $id               User B.
     * @param int $who              Shared user A.
     * @return array
     */
    public function getSharedFollows($id, $who) {

        $query = "SELECT DISTINCT F2.* FROM Follow AS F1, Follow AS F2 WHERE F1.fk_user = ? AND F1.fk_follows = F2.fk_user AND F2.fk_follows = ?";

        $result = $this->db->fetchAll(
            $query,
            array(
                (int) $id,
                (int) $who,
            ),
            array(\PDO::PARAM_INT, \PDO::PARAM_INT)
        );

        if (!$result) return []; // Any image in DB


        $followers = $this->populateFollows($result);

        return $followers;
    }


    /**
     * @param Application $app
     * @param Follow $row
     */
    public function update($row)
    {
        $query = "UPDATE `Follow` SET fk_user = ?, fk_follows = ? WHERE id = ?";
        $res = $this->db->executeUpdate(
            $query,
            array(
                $row->getFkUser(),
                $row->getFkFollows(),
                $row->getId()
            )
        );
    }

    public function remove($id)
    {
        $deletion = $this->db->delete(PdoFollowRepository::TABLE_NAME,
                        array(
                            'id' => $id
                        ));

        return $deletion;
    }

    public function length()
    {
        $result = $this->db->executeQuery("SELECT COUNT(*) AS total FROM Follow");

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

    /**
     * @param $queryResult
     * @return array
     */
    private function populateFollows($queryResult) {

        $follows = [];

        foreach ($queryResult as $follow) {

            array_push(
                $follows,
                new Follow(

                    $follow['fk_user'],
                    $follow['fk_follows'],
                    $follow['id']
                )
            );
        }

        return $follows;
    }

}