<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 14/04/17
 * Time: 00:38
 */

namespace pwgram\Model\Entity;


class ImageLike
{

    private $id;

    private $fkUser;

    private $fkImage;

    public function __construct($fkUser, $fkImage, $id = -1)
    {
        $this->id       = $id;
        $this->fkUser   = $fkUser;
        $this->fkImage  = $fkImage;
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
    public function getFkImage()
    {
        return $this->fkImage;
    }

    /**
     * @param mixed $fkImage
     */
    public function setFkImage($fkImage)
    {
        $this->fkImage = $fkImage;
    }



}