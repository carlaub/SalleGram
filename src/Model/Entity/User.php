<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 11/04/17
 * Time: 16:11
 */

namespace pwgram\Model\Entity;


class User {


    private $id;

    private $username;

    private $email;

    private $birthday;

    private $password; // TODO: se debería guardar en cliente?

    private $img_path; // Todo: se debería guardar en cliente?

    private $active;


    public function __construct($username, $email, $birthday, $active) {

        $this->username = $username;
        $this->email    = $email;
        $this->birthday = $birthday;
        $this->active   = $active;
    }

    public static function withImgPath($username, $email, $birthday, $active, $imgPath) {

        $instance = new self($username, $email, $birthday, $active);
        $instance->img_path = $imgPath;

        return $imgPath;
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
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getBirthday() {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday) {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getImgPath() {
        return $this->img_path;
    }

    /**
     * @param mixed $img_path
     */
    public function setImgPath($img_path) {
        $this->img_path = $img_path;
    }

    /**
     * @return mixed
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active) {
        $this->active = $active;
    }



}