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


    public function __construct($content, $fkUser, $lastModified, $fkImage) {

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
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param mixed $content
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
     * @return mixed
     */
    public function getFkUser() {
        return $this->fkUser;
    }

    /**
     * @param mixed $fkUser
     */
    public function setFkUser($fkUser) {
        $this->fkUser = $fkUser;
    }

    /**
     * @return mixed
     */
    public function getFkImage() {
        return $this->fkImage;
    }

    /**
     * @param mixed $fkImage
     */
    public function setFkImage($fkImage) {
        $this->fkImage = $fkImage;
    }




}