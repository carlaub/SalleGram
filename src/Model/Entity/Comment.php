<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 11/04/17
 * Time: 16:21
 */

namespace pwgram\Model\Entity;


class Comment {

    private $id;

    private $content;

    private $lastModified;

    private $fkUser;

    private $fkImage;


    public function __construct($content, $fkUser, $lastModified, $fkImage, $id = -1) {

        $this->id           = $id;
        $this->content      = $content;
        $this->fkUser       = $fkUser;
        $this->lastModified = $lastModified;
        $this->fkImage      = $fkImage;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getLastModified() {
        return $this->lastModified;
    }

    /**
     * @param mixed $lastModified
     */
    public function setLastModified($lastModified) {
        $this->lastModified = $lastModified;
    }

    /**
     * @return int
     */
    public function getFkUser() {
        return $this->fkUser;
    }

    /**
     * @param int $fkUser
     */
    public function setFkUser($fkUser) {
        $this->fkUser = $fkUser;
    }

    /**
     * @return int
     */
    public function getFkImage() {
        return $this->fkImage;
    }

    /**
     * @param int $fkImage
     */
    public function setFkImage($fkImage) {
        $this->fkImage = $fkImage;
    }




}