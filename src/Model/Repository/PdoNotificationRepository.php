<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 19/04/17
 * Time: 00:42
 */

namespace pwgram\Model\Repository;


use Doctrine\DBAL\Connection;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Notification;
use Silex\Application;

class PdoNotificationRepository implements PdoRepository
{

    const TABLE_NAME    = "Notification";

    private $db;


    /**
     * PdoNotificationRepository constructor.
     * @param Database|Connection $db
     */
    public function __construct( $db)
    {
        $this->db = $db;
    }

    public function add($row)
    {
        $this->db->insert(PdoNotificationRepository::TABLE_NAME,
                            array(
                                'fk_user_dest'  =>  $row->getWho(),
                                'fk_user_src'   =>  $row->getFrom(),
                                'type'          =>  $row->getType(),
                                'fk_image'      =>  $row->getWhere(),
                                'created_at'    =>  $row->getCreatedAt()
                            ));

        //return $result !== false;
    }

    public function get($id)
    {
        $query  = "SELECT * FROM `Notification` WHERE id = ?";
        $notification = $this->db->fetchAssoc($query, array($id));

        if (!$notification) return false; // an error happened during the execution

        return new Notification(
            $notification['fk_user_dest'],
            $notification['fk_user_src'],
            $notification['type'],
            $notification['fk_image'],
            $notification['created_at'],
            $notification['id']
        );
    }

    /**
     * @param int $id   The id of the user to recover his notifications
     *
     * @return array    An array of notifications for the user or empty if no one has been found.
     */
    public function getAllUserNotifications($id) {
        $notifications = [];

        $query  = "SELECT * FROM `Notification` WHERE fk_user_dest = ?";
        $result = $this->db->fetchAll($query, array($id));

        if (!$result) return $notifications; // an error happened during the execution

        foreach ($result as $notification) {

            array_push(
                $notifications,
                new Notification(
                    $notification['fk_user_dest'],
                    $notification['fk_user_src'],
                    $notification['type'],
                    $notification['fk_image'],
                    $notification['created_at'],
                    $notification['id']
                )
            );
        }

        return $notifications;
    }

    public function update($row)
    {
        $query = "UPDATE `Notification` SET fk_user_dest = ?, fk_user_src = ?, `type` = ?, fk_image = ?, created_at = ? WHERE id = ?";
        $res = $this->db->executeUpdate(
            $query,
            array(
                $row->getWho(),
                $row->getFrom(),
                $row->getType(),
                $row->getWhere(),
                $row->getCreatedAt(),
                $row->getId()
            )
        );
    }


    public function getNotificationsImage($id) {
        $notifications = [];

        $query  = "SELECT * FROM `Notification` WHERE fk_image = ?";
        $result = $this->db->fetchAll($query, array($id));

        if (!$result) return $notifications; // an error happened during the execution

        foreach ($result as $notification) {

            array_push(
                $notifications,
                new Notification(
                    $notification['fk_user_dest'],
                    $notification['fk_user_src'],
                    $notification['type'],
                    $notification['fk_image'],
                    $notification['created_at'],
                    $notification['id']
                )
            );
        }

        return $notifications;
    }




    public function remove($id)
    {
        $this->db->delete(PdoNotificationRepository::TABLE_NAME,
            array(
                'id' => $id
            ));
    }

    public function length()
    {
        $result = $this->db->executeQuery("SELECT COUNT(*) AS total FROM Notification");

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

}