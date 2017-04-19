<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 19/04/17
 * Time: 00:42
 */

namespace pwgram\Model\Repository;


use pwgram\lib\Database\Database;
use pwgram\Model\Entity\Notification;

class PdoNotificationRepository implements PdoRepository
{

    private $db;


    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function add($row)
    {
        $query  = "INSERT INTO Notification(`fk_user_dest`, `fk_user_src`, `type`, `fk_image`, `created_at`) VALUES(?, ?, ?, ?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getWho(),
                $row->getFrom(),
                $row->getType(),
                $row->getWhere(),
                $row->getCreatedAt()
            ]
        );

        return $result !== false;
    }

    public function get($id)
    {
        $query  = "SELECT * FROM `Notification` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $notification = $result->fetch();

        if (!$notification) return false;   // comment not found

        return new Notification(
            $notification['fk_user_dest'],
            $notification['fk_user_src'],
            $notification['type'],
            $notification['fk_image'],
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
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return $notifications; // an error happened during the execution

        $rawNotifications = $result->fetchAll();

        if (!$rawNotifications) return $notifications;

        foreach ($rawNotifications as $notification) {

            array_push(
                $notifications,
                new Notification(
                    $notification['fk_user_dest'],
                    $notification['fk_user_src'],
                    $notification['type'],
                    $notification['fk_image'],
                    $notification['id']
                )
            );
        }

        return $notifications;
    }

    public function update($row)
    {
        $query = "UPDATE `Notifcation` SET fk_user_dest = ?, fk_user_src = ?, `type` = ?, fk_image = ?, created_at = ? WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getWho(),
                $row->getFrom(),
                $row->getType(),
                $row->getWhere(),
                $row->createdAt(),
                $row->getId()
            ]
        );
    }

    public function remove($id)
    {
        $query = "DELETE FROM `Notification` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
    }

    public function length()
    {
        $query = "SELECT COUNT(*) AS total FROM Notification";
        $result = $this->db->query($query);

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

}