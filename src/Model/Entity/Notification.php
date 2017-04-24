<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 18/04/17
 * Time: 01:19
 */

namespace pwgram\Model\Entity;


class Notification
{

    const TYPE_COMMENT  = 1;
    const TYPE_LIKE     = 2;

    /**
     * @var int     id of the notification in the database.
     */
    private $id;

    /**
     * @var int     id of the user that <b>receives</b> the notification.
     */
    private $who;

    /**
     * @var int     id of the user who <b>caused</b> the notification
     */
    private $from;

    /**
     * @var int Type of notification: like or comment.
     *
     * type 0 -> LIKE
     * type 1 -> COMMENT
     */
    private $type;

    /**
     * @var int FK of the image liked or commented.
     */
    private $where;

    private $createdAt;


    public function __construct($who, $from, $type, $where, $createdAt, $id = -1)
    {
        $this->id           = $id;
        $this->who          = $who;
        $this->from         = $from;
        $this->type         = $type;
        $this->where        = $where;
        $this->createdAt    = $createdAt;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getWho(): int
    {
        return $this->who;
    }

    /**
     * @param int $who
     */
    public function setWho(int $who)
    {
        $this->who = $who;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param int $from
     */
    public function setFrom(int $from)
    {
        $this->from = $from;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getWhere(): int
    {
        return $this->where;
    }

    /**
     * @param int $where
     */
    public function setWhere(int $where)
    {
        $this->where = $where;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }



}