<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 13/04/17
 * Time: 19:20
 */

namespace pwgram\Model\Entity;


class Image
{

    private $id;

    private $title;

    private $imgPath;

    private $visits;

    private $private;

    private $createdAt;

    private $likes;

    private $userName;

    private $numComments;

    /**
     * @var array of Comment
     */
    private $comments;

    /**
     * @var User    the user who is the owner of the published photo
     */
    private $fkUser;

    /**
     * @var bool if user session likes the photo
     */
    private $liked;



    public function __construct($title, $createdAt, $fkUser, $private, $visits = 0, $likes = 0, $id = -1)
    {
        $this->id           = $id;
        $this->title        = $title;
        $this->createdAt    = $createdAt;
        $this->fkUser       = $fkUser;
        $this->private      = $private;
        $this->visits       = $visits;
        $this->likes        = $likes;
        $this->comments     = [];

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getImgPath()
    {
        return $this->imgPath;
    }

    /**
     * @param mixed $imgPath
     */
    public function setImgPath($imgPath)
    {
        $this->imgPath = $imgPath;
    }

    /**
     * @return int
     */
    public function getVisits(): int
    {
        return $this->visits;
    }

    /**
     * @param int $visits
     */
    public function setVisits(int $visits)
    {
        $this->visits = $visits;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private)
    {
        $this->private = $private;
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

    /**
     * @return int
     */
    public function getLikes(): int
    {
        return $this->likes;
    }

    /**
     * @param int $likes
     */
    public function setLikes(int $likes)
    {
        $this->likes = $likes;
    }

    /**
     * @return the
     */
    public function getFkUser()
    {
        return $this->fkUser;
    }

    /**
     * @param the $fkUser
     */
    public function setFkUser($fkUser)
    {
        $this->fkUser = $fkUser;
    }

    /**
     * @param $userName
     */
    public function setUserName($userName) {
        $this->userName = $userName;
    }

    public function getUserName() {
        return $this->userName;
    }

    /**
     * @return array
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array $comments
     */
    public function setComments(array $comments)
    {
        $this->comments = $comments;
        $this->numComments = count($comments);
    }

    /**
     * @param int $numComments
     */
    public function setNumComments(int $numComments) {
        $this->numComments = $numComments;
    }

    /**
     *
     */
    public function getNumComments() {
        return $this->numComments;
    }


    public function getLiked() {
        return $this->liked;

    }

    public function setLiked(bool $liked) {
        $this->liked = $liked;
    }


}