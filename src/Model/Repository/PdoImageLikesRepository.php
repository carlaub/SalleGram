<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 14/04/17
 * Time: 00:33
 */

namespace pwgram\Model\Repository;


use pwgram\Model\Entity\ImageLike;


// not checked
class PdoImageLikesRepository implements PdoRepository
{

    /**
     * @var Database class instance.
     */
    private $db;


    public function add($row)
    {
        $query  = "INSERT INTO Image_likes(`fk_user`, `fk_image`) VALUES(?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getFkUser(),
                $row->getFkImage()
            ]
        );

        return !$result;
    }

    public function get($id)
    {
        $query  = "SELECT id, fk_user, fk_image FROM `Image_likes` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $comment = $result->fetch();

        if (!$comment) return false;   // comment not found

        return new ImageLike(
            $comment['fk_user'],
            $comment['fk_image'],
            $comment['id']
        );
    }

    public function update($row)
    {
        $query = "UPDATE `Image_likes` SET fk_user = ?, fk_image = ? WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getFkUser(),
                $row->getFkImage(),
                $row->getId()
            ]
        );
    }

    public function remove($id)
    {
        $query = "DELETE FROM `Image_likes` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
    }

    public function length()
    {
        $query = "SELECT COUNT(*) AS total FROM Image_likes";
        $result = $this->db->query($query);

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }
}