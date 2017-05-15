<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 15/05/17
 * Time: 02:18
 */

namespace pwgram\Model\Entity;


class Follow
{

    private $id;

    private $fkUser;

    private $fkFollows;


    public function __construct($idUser, $idFollows, $id = -1)
    {

        $this->id           = $id;
        $this->fkUser       = $idUser;
        $this->fkFollows    = $idFollows;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFkUser()
    {
        return $this->fkUser;
    }

    /**
     * @param mixed $fkUser
     */
    public function setFkUser($fkUser)
    {
        $this->fkUser = $fkUser;
    }

    /**
     * @return mixed
     */
    public function getFkFollows()
    {
        return $this->fkFollows;
    }

    /**
     * @param mixed $fkFollows
     */
    public function setFkFollows($fkFollows)
    {
        $this->fkFollows = $fkFollows;
    }


}