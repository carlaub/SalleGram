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


    /**
     * Adds a follow into the database.
     *
     * @param Application $app
     * @param Follow $row
     */
    public function add(Application $app, $row)
    {

        $insert = $app['db']->insert(PdoFollowRepository::TABLE_NAME,
            array(
                'fk_user'  =>  $row->getFkUser(),
                'fk_follows'   =>  $row->getFkFollows(),
            ));

        return $insert;
    }

    public function get(Application $app, $id)
    {
        $query  = "SELECT * FROM `Follow` WHERE id = ?";
        $follow = $app['db']->fetchAssoc($query, array($id));

        if (!$follow) return false; // an error happened during the execution

        return new Follow(
            $follow['fk_user'],
            $follow['fk_follows'],
            $follow['id']
        );
    }

    public function getIsFollowedBy(Application $app, $follower, $followed) {

        $query  = "SELECT * FROM `Follow` WHERE fk_user = ? AND fk_follows = ?";
        $follow = $app['db']->fetchAssoc(
                        $query,
                        array($follower, $followed),
                        array(\PDO::PARAM_INT, \PDO::PARAM_INT));

        return $follow;
    }

    public function getUserFollows(Application $app, $id) {

        //SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = 1);
        $query = "SELECT * FROM Follow WHERE fk_user = ?";
        $result = $app['db']->fetchAll(
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
     * Returns all the users that follows a user A that is also followed by a specific user B.
     *
     * @param Application $app
     * @param int $id               User B.
     * @param int $who              Shared user A.
     * @return array
     */
    public function getSharedFollows(Application $app, $id, $who) {


        //SELECT * FROM Image WHERE id IN (SELECT fk_image FROM Comment WHERE fk_user = 1);
        $query = "SELECT * FROM Follow WHERE fk_follows = ? IN (SELECT fk_user FROM Follow WHERE fk_follows = ?)";
        $result = $app['db']->fetchAll(
            $query,
            array(
                (int) $who,
                (int) $id
            ),
            array(\PDO::PARAM_INT, \PDO::PARAM_INT)
        );

        if (!$result) return []; // Any image in DB

        return $this->populateFollows($result);
    }


    /**
     * @param Application $app
     * @param Follow $row
     */
    public function update(Application $app, $row)
    {
        $query = "UPDATE `Follow` SET fk_user = ?, fk_follows = ? WHERE id = ?";
        $res = $app['db']->executeUpdate(
            $query,
            array(
                $row->getFkUser(),
                $row->getFkFollows(),
                $row->getId()
            )
        );
    }

    public function remove(Application $app, $id)
    {
        $deletion = $app['db']->delete(PdoFollowRepository::TABLE_NAME,
                        array(
                            'id' => $id
                        ));

        return $deletion;
    }

    public function length(Application $app)
    {
        $result = $app['db']->executeQuery("SELECT COUNT(*) AS total FROM Follow");

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